#!/usr/bin/env node
const { spawn } = require('child_process');
const path = require('path');

const args = process.argv.slice(2);

// Try to use the Python package
const proc = spawn('screenshot-guard', args, {
  stdio: 'inherit',
  shell: true
});

proc.on('error', () => {
  console.error('Error: screenshot-guard Python package not found.');
  console.error('Install it with: pip install screenshot-guard');
  process.exit(1);
});

proc.on('close', (code) => {
  process.exit(code || 0);
});
