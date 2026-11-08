<?php
/**
 * Lanceur de tests sans Composer ni PHPUnit.
 *
 * Réutilise les classes `*UnitTest.php` de WPCS comme source des attentes
 * (getErrorList / getWarningList), exécute PHPCS sur chaque fixture `.inc`
 * et compare le résultat ligne par ligne.
 *
 * Usage : php bin/test.php [filtre]    ex. php bin/test.php I18n
 *
 * Limites : ne vérifie pas les fichiers `.inc.fixed` (phpcbf) ni les tests
 * qui n'utilisent pas getErrorList() / getWarningList() (ex. testStdIn).
 */

namespace PHP_CodeSniffer\Tests\Standards {
	// Substitut minimal : les classes de test n'ont besoin que d'un parent.
	class AbstractSniffUnitTest {}
}

namespace {
	/**
	 * Substitut de PHP_CodeSniffer\\Config : enregistre les valeurs que les tests
	 * veulent passer en ligne de commande (setCliValues) pour les rejouer via --runtime-set.
	 */
	class Retraceur_Config_Recorder {
		public $values = array();

		public function setConfigData( $key, $value, $temp = false ) {
			if ( null === $value ) {
				unset( $this->values[ $key ] );
			} else {
				$this->values[ $key ] = (string) $value;
			}
		}
	}

	$root   = dirname( __DIR__ );
	$deps   = $root . '/.deps';
	$phar   = $deps . '/phpcs.phar';
	$filter = $argv[1] ?? '';

	if ( ! is_file( $phar ) ) {
		fwrite( STDERR, "phpcs.phar introuvable : lancez d'abord `node bin/setup.mjs`.\n" );
		exit( 2 );
	}

	$installed_paths = implode( ',', array( $deps . '/PHPCSUtils', $root ) );

	/**
	 * Lance PHPCS sur un fichier et retourne les messages par ligne.
	 */
	function run_phpcs( $phar, $installed_paths, $sniff, $file, array $runtime = array() ) {
		$extra = '';
		foreach ( $runtime as $key => $value ) {
			$extra .= ' --runtime-set ' . escapeshellarg( $key ) . ' ' . escapeshellarg( $value );
		}
		$cmd = sprintf(
			'PHPCSUTILS_PATH=%s %s -d auto_prepend_file=%s %s --runtime-set installed_paths %s --standard=Retraceur --sniffs=%s --extensions=inc,php,php3 --report=json -q --no-cache%s %s 2>&1',
			escapeshellarg( dirname( $phar ) . '/PHPCSUtils' ),
			escapeshellarg( PHP_BINARY ),
			escapeshellarg( __DIR__ . '/test-prepend.php' ),
			escapeshellarg( $phar ),
			escapeshellarg( $installed_paths ),
			escapeshellarg( $sniff ),
			$extra,
			escapeshellarg( $file )
		);
		$out  = shell_exec( $cmd );
		$data = json_decode( (string) $out, true );
		if ( ! is_array( $data ) ) {
			return array( null, $out );
		}
		$result = array( 'ERROR' => array(), 'WARNING' => array() );
		foreach ( $data['files'] as $info ) {
			foreach ( $info['messages'] as $m ) {
				$result[ $m['type'] ][ $m['line'] ] = ( $result[ $m['type'] ][ $m['line'] ] ?? 0 ) + 1;
			}
		}
		return array( $result, '' );
	}

	function normalize( array $list ) {
		$list = array_filter( $list );
		ksort( $list );
		return $list;
	}

	$passed   = 0;
	$failed   = 0;
	$skipped  = 0;
	$failures = array();

	$tests = glob( $root . '/Retraceur/Tests/*/*UnitTest.php' );
	sort( $tests );

	foreach ( $tests as $test_php ) {
		$cat  = basename( dirname( $test_php ) );
		$name = basename( $test_php, 'UnitTest.php' );
		if ( '' !== $filter && false === stripos( "$cat.$name", $filter ) ) {
			continue;
		}

		require_once $test_php;
		$class = "PHPCodingStandards\\Retraceur\\Tests\\{$cat}\\{$name}UnitTest";
		$ref   = new ReflectionClass( $class );
		$obj   = $ref->newInstanceWithoutConstructor();
		$base  = dirname( $test_php ) . '/' . $name . 'UnitTest';
		$sniff = "Retraceur.$cat.$name";

		if ( $ref->hasMethod( 'getTestFiles' ) ) {
			$m = $ref->getMethod( 'getTestFiles' );
			$m->setAccessible( true );
			$files = $m->invoke( $obj, $base );
		} else {
			$files = array_merge( glob( $base . '.inc' ), glob( $base . '.*.inc' ) );
		}
		sort( $files );

		foreach ( $files as $file ) {
			$label = "$sniff  " . ltrim( str_replace( dirname( $test_php ), '', $file ), '/' );
			$recorder = new Retraceur_Config_Recorder();
			if ( $ref->hasMethod( 'setCliValues' ) ) {
				$obj->setCliValues( basename( $file ), $recorder );
			}
			list( $actual, $raw ) = run_phpcs( $phar, $installed_paths, $sniff, $file, $recorder->values );

			if ( null === $actual ) {
				++$failed;
				$failures[] = "$label\n    sortie PHPCS illisible : " . trim( substr( $raw, 0, 300 ) );
				continue;
			}

			$base_name = basename( $file );
			$exp_err   = normalize( $obj->getErrorList( $base_name ) );
			$exp_warn  = normalize( $obj->getWarningList( $base_name ) );
			$act_err   = normalize( $actual['ERROR'] );
			$act_warn  = normalize( $actual['WARNING'] );

			if ( $exp_err === $act_err && $exp_warn === $act_warn ) {
				++$passed;
				continue;
			}

			++$failed;
			$diff = '';
			foreach ( array( 'erreurs' => array( $exp_err, $act_err ), 'avertissements' => array( $exp_warn, $act_warn ) ) as $kind => $pair ) {
				if ( $pair[0] !== $pair[1] ) {
					$diff .= sprintf( "\n    %s attendues : %s\n    %s obtenues  : %s", $kind, json_encode( $pair[0] ), $kind, json_encode( $pair[1] ) );
				}
			}
			$failures[] = $label . $diff;
		}
	}

	// Rulesets : Retraceur-Coeur et Retraceur-Extra se chargent et activent les bons sniffs.
	if ( '' === $filter || false !== stripos( 'rulesets', $filter ) ) {
		$sample = $root . '/Retraceur/Tests/RulesetCheck/sample.inc';
		$checks = array(
			'Retraceur-Coeur'  => array(
				'present' => array( 'Retraceur.NamingConventions.ValidFunctionName.FunctionNameInvalid', 'Retraceur.NamingConventions.ValidHookName.UseUnderscores' ),
				'absent'  => array( 'Retraceur.PHP.DevelopmentFunctions', 'Retraceur.PHP.NoSilencedErrors' ),
				'lines'   => array( 'Retraceur.NamingConventions.ValidHookName.UseUnderscores' => array( 4 ) ),
			),
			'Retraceur-Extra' => array(
				'present' => array( 'Retraceur.NamingConventions.ValidFunctionName.FunctionNameInvalid', 'Retraceur.PHP.DevelopmentFunctions.error_log_error_log', 'Retraceur.PHP.NoSilencedErrors.Discouraged' ),
				'absent'  => array(),
				'lines'   => array( 'Retraceur.NamingConventions.ValidHookName.UseUnderscores' => array( 4 ) ),
			),
		);
		foreach ( $checks as $standard => $expect ) {
			$cmd  = sprintf(
				'%s %s --runtime-set installed_paths %s --standard=%s --report=json -q --no-cache --extensions=inc %s 2>&1',
				escapeshellarg( PHP_BINARY ),
				escapeshellarg( $phar ),
				escapeshellarg( $installed_paths ),
				escapeshellarg( $standard ),
				escapeshellarg( $sample )
			);
			$data = json_decode( (string) shell_exec( $cmd ), true );
			$seen = array();
			if ( is_array( $data ) ) {
				foreach ( $data['files'] as $info ) {
					foreach ( $info['messages'] as $m ) {
						$seen[ $m['source'] ][] = $m['line'];
					}
				}
			}
			$problems = array();
			if ( ! is_array( $data ) ) {
				$problems[] = 'sortie PHPCS illisible';
			}
			foreach ( $expect['present'] as $code ) {
				if ( ! isset( $seen[ $code ] ) ) {
					$problems[] = "code attendu absent : $code";
				}
			}
			foreach ( $expect['absent'] as $prefix ) {
				foreach ( array_keys( $seen ) as $code ) {
					if ( 0 === strpos( $code, $prefix ) ) {
						$problems[] = "code inattendu : $code";
					}
				}
			}
			foreach ( $expect['lines'] as $code => $lines ) {
				if ( isset( $seen[ $code ] ) && $seen[ $code ] !== $lines ) {
					$problems[] = "$code : lignes " . json_encode( $seen[ $code ] ) . ', attendu ' . json_encode( $lines ) . ' (phpcs:ignore non respecté ?)';
				}
			}
			if ( array() === $problems ) {
				++$passed;
			} else {
				++$failed;
				$failures[] = "ruleset $standard\n    " . implode( "\n    ", $problems );
			}
		}
	}

	foreach ( $failures as $f ) {
		echo "ÉCHEC  $f\n";
	}
	printf( "\n%d fixture(s) OK, %d en échec.\n", $passed, $failed );
	exit( $failed > 0 ? 1 : 0 );
}
