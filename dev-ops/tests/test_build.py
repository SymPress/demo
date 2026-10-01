import json
import os
from pathlib import Path
import shutil
import subprocess
import tempfile
import unittest


class DeploymentBuildTest(unittest.TestCase):
    def test_root_orchestrator_is_excluded_from_asset_discovery(self):
        manifest = json.loads((Path(__file__).parents[2] / 'composer.json').read_text())
        selection = manifest['extra']['sympress']['asset-compiler']['packages']
        self.assertIs(selection['sympress/demo'], False)
        self.assertIs(selection['sympress/demo-plugin'], True)

    def test_nested_build_stops_before_composer_mutates_dependencies(self):
        with tempfile.TemporaryDirectory() as directory:
            root = Path(directory)
            (root / 'dev-ops').mkdir()
            shutil.copyfile(Path(__file__).parents[1] / 'build.php', root / 'dev-ops/build.php')
            composer = root / 'composer'
            composer.write_text('#!/bin/sh\ntouch "' + str(root / 'mutated') + '"\n')
            composer.chmod(0o700)
            for marker in ['1', '0']:
                env = dict(os.environ, SYMPRESS_ASSET_COMPILER_ACTIVE=marker, PATH=str(root) + ':' + os.environ['PATH'])
                result = subprocess.run(['php', str(root / 'dev-ops/build.php')], env=env, capture_output=True, text=True)
                self.assertEqual(result.returncode, 1)
                self.assertIn('cannot run inside an asset compilation', result.stderr)
                self.assertFalse((root / 'mutated').exists())


if __name__ == '__main__':
    unittest.main()
