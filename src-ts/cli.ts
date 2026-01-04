#!/usr/bin/env node
import { spawn } from 'child_process';

const args = process.argv.slice(2);

const proc = spawn('screenshot-guard', args, {
  stdio: 'inherit',
  shell: true
});

proc.on('error', () => {
  console.error('Error: screenshot-guard Python package not found.');
  console.error('Install with: pip install screenshot-guard');
  process.exit(1);
});

proc.on('close', (code) => {
  process.exit(code || 0);
});
