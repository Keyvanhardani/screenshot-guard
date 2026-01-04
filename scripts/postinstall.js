#!/usr/bin/env node
const { execSync } = require('child_process');

console.log('Installing screenshot-guard Python package...');

try {
  execSync('pip install screenshot-guard', { stdio: 'inherit' });
  console.log('screenshot-guard installed successfully!');
} catch (error) {
  console.warn('Note: Python package not auto-installed.');
  console.warn('Install manually: pip install screenshot-guard');
}
