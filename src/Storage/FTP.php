<?php

namespace Haley\Storage;

class FTP
{
    private string $connection = 'default';

    public function connection()
    {

        return new self;
    }

    public function test()
    {
        $servidor = '154.49.247.11'; // Endereço
        $usuario = 'u921294813'; // Usuário
        $senha = ''; // Senha
        $porta = 21; // Porta padrão
        $timeout = 9000; // Tempo em segundos para encerrar a conexão caso não haja resposta

        $ftp = ftp_connect($servidor, $porta, $timeout); // Retorno: true ou false

        if (!$ftp) return false;

        $login = ftp_login($ftp, $usuario, $senha); // Retorno: true ou false

        if (!$login) return false;

        // Alterna o modo de conexão para PASSIVO. No modo passivo, as conexões de dados são iniciadas pelo cliente, ao invés do servidor. Pode ser necessário se o cliente estiver atrás de um firewall.
        ftp_pasv($ftp, true);

        // lista arquivos
        $arquivos = ftp_nlist($ftp, 'domains/onflix.top/public_html');

        // Faz o download do arquivo no modo BINÁRIO (Deve ser FTP_ASCII ou FTP_BINARY.)
        $download = ftp_get($ftp, directoryRoot('private/teste/ftp_test.php'), 'domains/onflix.top/public_html/router.php', FTP_BINARY); // Retorno: true / false  

        dd($arquivos, $download, ftp_close($ftp));
    }

    public function mysqlBackup()
    {
        // Dados de conexão com o banco de dados
        $host = 'srv952.hstgr.io';
        $username = 'u921294813_mobilex';
        $database = 'u921294813_mobilex';
        $password = '';     

        // Nome do arquivo de backup
        $backupFile = directoryRoot('private/teste/backup.sql');

        // Comando para execução do mysqldump
        $command = "mysqldump --host=$host --user=$username --password=$password $database > $backupFile";

        // Executar o comando do mysqldump
        system($command, $output);

        dd($output == 0 ? true : false);
    }
}
