// Télécharge PHPCS, PHPCSUtils et PHPCSExtra dans .deps/ (idempotent, checksums vérifiés).
import { mkdirSync, existsSync, writeFileSync, readFileSync, rmSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { execFileSync } from 'node:child_process';
import { dirname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '..');
const dir = join(root, '.deps');
const deps = JSON.parse(readFileSync(join(root, 'phpcs-deps.json'), 'utf8'));
const stampFile = join(dir, '.installed.json');
const stamp = existsSync(stampFile) ? JSON.parse(readFileSync(stampFile, 'utf8')) : {};

mkdirSync(dir, { recursive: true });

for (const [name, dep] of Object.entries(deps)) {
  const target = join(dir, name === 'phpcs' ? 'phpcs.phar' : name);
  if (stamp[name] === dep.version && existsSync(target)) continue;

  console.log(`[setup] ${name} ${dep.version}…`);
  const res = await fetch(dep.url);
  if (!res.ok) throw new Error(`${name}: HTTP ${res.status} sur ${dep.url}`);
  const buf = Buffer.from(await res.arrayBuffer());
  const actual = createHash('sha256').update(buf).digest('hex');
  if (actual !== dep.sha256) {
    throw new Error(`${name}: checksum invalide\n  attendu: ${dep.sha256}\n  obtenu : ${actual}`);
  }

  if (name === 'phpcs') {
    writeFileSync(target, buf);
  } else {
    const archive = join(dir, `${name}.tar.gz`);
    rmSync(target, { recursive: true, force: true });
    mkdirSync(target, { recursive: true });
    writeFileSync(archive, buf);
    execFileSync('tar', ['-xzf', archive, '-C', target, '--strip-components=1']);
    rmSync(archive);
  }
  stamp[name] = dep.version;
}

writeFileSync(stampFile, JSON.stringify(stamp, null, 2));
console.log('[setup] prêt.');
