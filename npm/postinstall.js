const { execSync } = require('child_process');

console.log('Installing screenshot-guard Python package...');

try {
  execSync('pip install screenshot-guard', { stdio: 'inherit' });
  console.log('screenshot-guard installed successfully!');
} catch (error) {
  console.warn('Could not auto-install Python package.');
  console.warn('Please install manually: pip install screenshot-guard');
}
