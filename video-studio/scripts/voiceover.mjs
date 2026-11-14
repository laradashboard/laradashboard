// Generates one ElevenLabs voice clip per scene from each scene's `vo` text, then writes
// voFile + voDuration back into storyboard.json (scene length then stretches to fit the voice).
// Usage: node scripts/voiceover.mjs <project> [--force]   (reads video-studio/.env, or the shell env)
// Cached by text+voice hash, so re-running only generates changed lines.
import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
import { ROOT, normalizeLoudness, probeDuration, projectDir } from './lib.mjs';

// Load video-studio/.env (ELEVENLABS_API_KEY, ELEVENLABS_VOICE_ID, ELEVENLABS_MODEL); shell env still wins.
const envFile = path.join(ROOT, '.env');
if (fs.existsSync(envFile)) for (const line of fs.readFileSync(envFile, 'utf8').split('\n')) {
  const m = line.match(/^\s*([A-Z0-9_]+)\s*=\s*(.*?)\s*$/);
  if (m && !process.env[m[1]]) process.env[m[1]] = m[2].replace(/^(['"])(.*)\1$/, '$2');
}

const args = process.argv.slice(2);
const dir = projectDir(args.find((a) => !a.startsWith('--')));
const force = args.includes('--force');
const key = process.env.ELEVENLABS_API_KEY;
const voice = process.env.ELEVENLABS_VOICE_ID ?? '21m00Tcm4TlvDq8ikWAM';
const model = process.env.ELEVENLABS_MODEL ?? 'eleven_multilingual_v2';
if (!key) { console.error('Set ELEVENLABS_API_KEY'); process.exit(1); }

const file = path.join(dir, 'storyboard.json');
const sb = JSON.parse(fs.readFileSync(file, 'utf8'));
fs.mkdirSync(path.join(dir, 'vo'), { recursive: true });
let chars = 0;
for (const [i, s] of sb.scenes.entries()) {
  if (!s.vo) { delete s.voFile; delete s.voDuration; continue; }
  const hash = crypto.createHash('sha1').update(`${voice}|${model}|${s.vo}`).digest('hex').slice(0, 8);
  const rel = `vo/${String(i).padStart(2, '0')}-${hash}.mp3`;
  const abs = path.join(dir, rel);
  if (force || !fs.existsSync(abs)) {
    const res = await fetch(`https://api.elevenlabs.io/v1/text-to-speech/${voice}?output_format=mp3_44100_192`, {
      method: 'POST',
      headers: { 'xi-api-key': key, 'Content-Type': 'application/json', Accept: 'audio/mpeg' },
      body: JSON.stringify({ text: s.vo, model_id: model, voice_settings: { stability: 0.45, similarity_boost: 0.8, style: 0.25, use_speaker_boost: true } }),
    });
    if (!res.ok) { console.error(`✗ scene ${i}: ${res.status} ${await res.text()}`); process.exit(1); }
    const raw = abs.replace(/\.mp3$/, '.raw.mp3');
    fs.writeFileSync(raw, Buffer.from(await res.arrayBuffer()));
    normalizeLoudness(raw, abs); // ElevenLabs output is ~-21 LUFS; bring voice to -16 LUFS
    fs.rmSync(raw);
    chars += s.vo.length;
    console.log(`✓ scene ${i} -> ${rel}`);
  }
  s.voFile = rel;
  s.voDuration = +probeDuration(abs).toFixed(2);
}
fs.writeFileSync(file, JSON.stringify(sb, null, 2) + '\n');
console.log(`Done. ${chars} characters sent to ElevenLabs.`);
