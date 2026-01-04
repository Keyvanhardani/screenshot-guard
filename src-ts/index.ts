import { spawn } from 'child_process';

export interface ScanOptions {
  path: string;
  format?: 'table' | 'json' | 'sarif' | 'markdown';
  severity?: 'critical' | 'high' | 'medium' | 'low' | 'all';
  ocr?: boolean;
  backend?: 'llamacpp' | 'ollama' | 'cloud';
  output?: string;
}

export interface Finding {
  file: string;
  line: number;
  type: string;
  severity: string;
  match: string;
  fromOcr: boolean;
}

export async function scan(options: ScanOptions): Promise<Finding[]> {
  return new Promise((resolve, reject) => {
    const args = ['scan', options.path];

    if (options.format) {
      args.push('--format', options.format);
    }
    if (options.severity) {
      args.push('--severity', options.severity);
    }
    if (options.ocr === false) {
      args.push('--no-ocr');
    }
    if (options.backend) {
      args.push('--backend', options.backend);
    }
    if (options.output) {
      args.push('--output', options.output);
    }

    // Force JSON output for parsing
    if (!options.format) {
      args.push('--format', 'json');
    }

    const proc = spawn('screenshot-guard', args, { shell: true });
    let stdout = '';
    let stderr = '';

    proc.stdout.on('data', (data) => {
      stdout += data.toString();
    });

    proc.stderr.on('data', (data) => {
      stderr += data.toString();
    });

    proc.on('close', (code) => {
      if (options.format && options.format !== 'json') {
        // Non-JSON format, just return empty
        resolve([]);
        return;
      }

      try {
        const result = JSON.parse(stdout);
        const findings: Finding[] = (result.findings || []).map((f: any) => ({
          file: f.file_path,
          line: f.line_number,
          type: f.pattern_name,
          severity: f.severity,
          match: f.match,
          fromOcr: f.from_ocr
        }));
        resolve(findings);
      } catch {
        resolve([]);
      }
    });

    proc.on('error', (err) => {
      reject(new Error(`screenshot-guard not found. Install with: pip install screenshot-guard`));
    });
  });
}

export default { scan };
