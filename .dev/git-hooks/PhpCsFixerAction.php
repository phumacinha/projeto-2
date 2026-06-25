<?php

declare(strict_types=1);

namespace App\GitHooks;

use CaptainHook\App\Config;
use CaptainHook\App\Console\IO;
use CaptainHook\App\Exception\ActionFailed;
use CaptainHook\App\Hook\Action;
use SebastianFeldmann\Git\Repository;

final class PhpCsFixerAction implements Action {
    public function execute(
        Config $config,
        IO $io,
        Repository $repository,
        Config\Action $action
    ): void {
        $staged = $repository->getIndexOperator()->getStagedFiles();
        $phpFiles = $this->filterPhpFiles($staged);

        if (empty($phpFiles)) {
            return; // Nenhum arquivo PHP staged; nada a fazer.
        }

        $options = $action->getOptions();
        $binary = $this->resolveBinary((string) ($options->get('binary') ?? 'vendor/bin/php-cs-fixer'));
        $cfgFile = (string) ($options->get('config') ?? '.php-cs-fixer.php');
        $autoFix = (bool) ($options->get('auto_fix') ?? false);

        $count = count($phpFiles);
        $io->write(sprintf('<info>php-cs-fixer: analisando %d arquivo(s)...</info>', $count));

        if ($autoFix) {
            $this->applyFix($binary, $cfgFile, $phpFiles, $io);
        } else {
            $this->checkOnly($binary, $cfgFile, $phpFiles, $io);
        }
    }

    // -------------------------------------------------------------------------
    // Modo check (dry-run)
    // -------------------------------------------------------------------------

    /** @param string[] $files */
    private function checkOnly(string $binary, string $cfgFile, array $files, IO $io): void {
        $violations = [];

        foreach ($files as $file) {
            [$exitCode, $output] = $this->runCsFixer($binary, $cfgFile, $file, false);

            if ($exitCode !== 0) {
                $violations[$file] = $output;
            }
        }

        if (empty($violations)) {
            $io->write('<info>php-cs-fixer: ✔ Todos os arquivos staged estão em conformidade.</info>');

            return;
        }

        $io->write('');
        foreach ($violations as $file => $output) {
            $io->write("<comment>── {$file}</comment>");
            $io->write($output);
            $io->write('');
        }

        throw new ActionFailed(sprintf(
            'php-cs-fixer encontrou violações em %d arquivo(s). '
            .'Corrija com "php vendor/bin/php-cs-fixer fix" e faça o stage novamente.',
            count($violations)
        ));
    }

    // -------------------------------------------------------------------------
    // Modo auto-fix
    // -------------------------------------------------------------------------

    /** @param string[] $files */
    private function applyFix(string $binary, string $cfgFile, array $files, IO $io): void {
        $errors = [];

        foreach ($files as $file) {
            [$exitCode, $output] = $this->runCsFixer($binary, $cfgFile, $file, true);

            // Exit 1 = exceção interna do php-cs-fixer (erro real).
            // Outros códigos != 0 indicam apenas que arquivos foram corrigidos.
            if ($exitCode === 1) {
                $errors[$file] = $output;
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $file => $output) {
                $io->write("<error>Erro ao corrigir {$file}: {$output}</error>");
            }

            throw new ActionFailed('php-cs-fixer encontrou erros ao corrigir os arquivos.');
        }

        // Re-faz o stage dos arquivos PHP (inclui os que foram modificados pelo fixer).
        foreach ($files as $file) {
            exec(sprintf('git add %s', escapeshellarg($file)));
        }

        $io->write('<info>php-cs-fixer: ✔ Arquivos corrigidos e re-staged com sucesso.</info>');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Filtra apenas arquivos .php que existem no disco
     * (exclui arquivos deletados do staged area).
     *
     * @param string[] $files
     *
     * @return string[]
     */
    private function filterPhpFiles(array $files): array {
        return array_values(array_filter(
            $files,
            function (string $f): bool {
                return pathinfo($f, PATHINFO_EXTENSION) === 'php' && file_exists($f);
            }
        ));
    }

    /**
     * Resolve o caminho correto do binário por sistema operacional.
     *
     * - Windows : tenta vendor\bin\php-cs-fixer.bat / .cmd
     * - Unix/Mac: usa o caminho como está (shell script com +x)
     *
     * @param string $binary
     */
    private function resolveBinary(string $binary): string {
        if (PHP_OS_FAMILY === 'Windows') {
            foreach ([$binary.'.bat', $binary.'.cmd'] as $candidate) {
                $winPath = str_replace('/', DIRECTORY_SEPARATOR, $candidate);
                if (file_exists($winPath)) {
                    return $winPath;
                }
            }
        }

        return $binary;
    }

    /**
     * Monta e executa o comando do php-cs-fixer.
     *
     * Estratégia cross-platform para invocar o binário:
     *   - Windows          → chama o .bat diretamente
     *   - Unix/Mac (+x)    → chama o shell script diretamente
     *   - Unix/Mac (sem +x)→ chama via PHP_BINARY como fallback
     *
     * @param string $binary
     * @param string $cfgFile
     * @param string $file
     * @param bool $fix
     *
     * @return array{int, string}
     */
    private function runCsFixer(string $binary, string $cfgFile, string $file, bool $fix): array {
        $subCmd = $fix ? 'fix' : 'fix --dry-run --diff';

        $exec = (PHP_OS_FAMILY !== 'Windows' && !is_executable($binary))
            ? escapeshellarg(PHP_BINARY).' '.escapeshellarg($binary)
            : escapeshellarg($binary);

        $command = sprintf(
            '%s %s --using-cache=no --config=%s -- %s 2>&1',
            $exec,
            $subCmd,
            escapeshellarg($cfgFile),
            escapeshellarg($file)
        );

        exec($command, $lines, $exitCode);

        return [$exitCode, implode(PHP_EOL, $lines)];
    }
}
