<?php
 
class usePDO
{
    private $host = 'localhost';
    private $db = 'tarefa_do_jean';
    private $port = 3306;
    private $user = 'root';
    private $pass = '';
    private $instance;
 
    function getInstance()
    {
        if (empty($this->instance)){
            $this->instance = $this->connection();
        }
        return $this->instance;
    }
 
    function connection()
    {
        try{
            $conn = new PDO("mysql:host=$this->host;port=$this->port;dbname=$this->db;charset=utf8", $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            error_log("Erro de conexão: " . $e->getMessage());
            throw new Exception("Não foi possível conectar ao banco de dados: " . $e->getMessage());
        }
    }
}
 