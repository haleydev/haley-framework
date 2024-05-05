<?php

namespace Haley\Shell;

class Shell extends Lines
{
    /**
     * @param callback function (string $line, array $status) ...
     * @return bool|int false = error | true = success and process is closed | int = success and process is running
     */
    public static function exec(string $command, callable|null $callback = null, string|null $name = null, string|null $description = null)
    {
        $process = proc_open($command, [
            0 => ['pipe', 'r'], // Entrada padrão do processo
            1 => ['pipe', 'w'], // Saída padrão do processo
            2 => ['pipe', 'w']  // Saída de erro do processo
        ], $pipes);

        // Verifica se o processo foi aberto com sucesso
        if (!is_resource($process)) return false;

        $status = proc_get_status($process);

        $cache_path = directoryRoot('storage/cache/jsons/shell.json');
        $cache = [];
        $pid = $status['pid'] + 1;

        if (file_exists($cache_path)) $cache = json_decode(file_get_contents($cache_path), true);

        $cache[$pid] = [
            'command' => $command,
            'name' => $name,
            'pid' => $pid,
            'description' => $description
        ];

        file_put_contents($cache_path, json_encode($cache));

        // Configura a saída padrão e de erro para não bloquear
        stream_set_blocking($pipes[1], 0);
        stream_set_blocking($pipes[2], 0);

        // Loop para ler a saída do processo em tempo real
        while (true) {
            $status = proc_get_status($process);

            if ($callback) {
                // Cria um array de streams para monitorar
                $read = array($pipes[1], $pipes[2]);
                $write = NULL;
                $except = NULL;
                $line = '';

                // Espera até que os streams estejam prontos para leitura
                if (stream_select($read, $write, $except, 0)) {
                    // Lê a saída do processo
                    foreach ($read as $stream) {
                        $line .= fread($stream, 1024);

                        if (strpos($line, "\n") !== false && $line !== '') {

                            executeCallable($callback, ['line' => trim($line), 'status' => $status]);

                            $line = '';
                        }

                        flush();
                    }
                }
            }

            // Verifica se o processo terminou
            if (!$status['running']) break;

            // Dá um pequeno intervalo de tempo para evitar uso excessivo da CPU
            usleep(10000);
        }

        // Fecha os pipes
        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        // Fecha o processo
        proc_close($process);

        if (posix_kill($pid, 0)) return $pid;

        return true;
    }

    /**
     * @return bool
     */
    public static function kill(int|array $pid)
    {
        if (!is_array($pid)) $pid = [$pid];

        $response = false;

        foreach ($pid as $value) {
            if (!is_numeric($value)) continue;

            $kill = posix_kill($value, SIGTERM);

            if ($kill) $response = true;
        }

        return $response;
    }

    /**
     * @return array
     */
    public static function pids()
    {
        $cache_path = directoryRoot('storage/cache/jsons/shell.json');
        $cache = [];

        if (file_exists($cache_path)) {
            $cache = json_decode(file_get_contents($cache_path), true);

            $save = false;

            foreach (array_keys($cache) as $pid) if (!posix_kill($pid, 0)) {
                unset($cache[$pid]);

                $save = true;
            }

            if ($save) file_put_contents($cache_path, json_encode($cache));
        }

        return $cache;
    }

    public static function readline()
    {
        return readline('') ?? '';
    }

    public static function memory(int $pid)
    {
        if (!posix_kill($pid, 0)) return null;
    }
}
