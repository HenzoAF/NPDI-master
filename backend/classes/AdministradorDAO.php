<?php
class AdministradorDAO{

    private function __construct(){
    }

//--------------------Função que instancia a classe DAO-----------------------//
    static function getInstance() {
      static $instance = null;
      if (null === $instance) {
          $instance = new static();
      }
      return $instance;
    }
//------------------------Funções de login e logout---------------------------//
// Login
    function validate($login, $senha){
      session_start();
      $_SESSION['administrador'] = null;
      session_destroy();
      $connection = $this->getConnection();

      $sql = "SELECT *
              FROM administradores
              WHERE login = '".$login."' && senha = '".crypt($senha, 'DcCnPd1')."'";

      $resultado = mysqli_query($connection, $sql);
      $linhas = mysqli_num_rows($resultado);
      if ($linhas != 1) {
        throw new ValidateException();
      }
      else{
        $data = mysqli_fetch_assoc($resultado);
        mysqli_close($connection);
        $administrador = new Administrador($data);
        $administrador->setId($data['id']);
        session_start();
        $_SESSION['administrador'] = $administrador->getId();
      }

      if(!isset($_SESSION['administrador'])){
        throw new ValidateException();
      }
      else return $administrador;

    }
// Logout
    function logout(){
      session_start();
      if(isset($_SESSION['administrador'])){
        $_SESSION['administrador'] = null;
        session_destroy();
        echo "you are loged out.";
      }
      else throw new LogoutException();
    }
//----------------------------Funções de cadastro-----------------------------//
//Verifica a disponibilidade das credenciais
    function verify($login){
      $connection = $this->getConnection();

      $sql = "SELECT *
              FROM administradores
              WHERE login = '".$login."'";

      $resultado = mysqli_query($connection, $sql);
      $linhas = mysqli_num_rows($resultado);
      mysqli_close($connection);
      if($linhas == 0){
        return true;
      }
      else return false;
    }
//Chama as funções para o cadastro do usuário
    function insert($array){

      session_start();
      if(isset($_SESSION['administrador'])){
        $novoAdministrador = new Administrador($array);
      }
      else throw new InsertionException();

      if ($this->verify($array['login']) && $this->validateEmail($array['email'])) {
        $connection = $this->getConnection();
        $resultado = mysqli_query($connection, "SELECT * FROM administradores");
        $linhas_ant = mysqli_num_rows($resultado);

        $sql = "INSERT INTO administradores (login, senha, primeiro_nome, sobre_nome, email)
                VALUES ('".$novoAdministrador->getLogin()."',
                        '".$novoAdministrador->getSenha()."',
                        '".$novoAdministrador->getPrimeiroNome()."',
                        '".$novoAdministrador->getSobreNome()."',
                        '".$novoAdministrador->getEmail()."')";

        $action = mysqli_query($connection, $sql);

        $resultado = mysqli_query($connection, "SELECT * FROM administradores");
        $linhas_pos = mysqli_num_rows($resultado);
        mysqli_close($connection);

        if($linhas_pos == $linhas_ant + 1){
          return $novoAdministrador;
        }
        else throw new InsertionException();
      }

      else throw new InsertionException();
    }
//Método para fazer upload da imagem do administrador
  function uploadImage($file){
    //if(isset($_SESSION['administrador'])){
      if($file != null){
        //Verificando requisitos da imagem
        $upload = true;
        $extensao = pathinfo($file["name"],PATHINFO_EXTENSION);
        $nomeFinal = "../../frontend/img/perfil/".$_SESSION['administrador'].".".$extensao;

        if ($file["size"] > 5120000) {
           $upload = false;
        }
        if($extensao != "jpg" && $extensao != "png" && $extensao != "jpeg" && $extensao != "bmp") {
            $upload = false;
        }
        if (!$upload) {
          throw new InsertionException();
        }

        //Organizando imagem no servidor
        move_uploaded_file($file['tmp_name'], $nomeFinal);
        $img = fopen($nomeFinal, "r") or die ("A imgem não foi inserida corretamente");
        fclose($img);
      }
    //}
    //else throw new InsertionException();
  }
//------------------Funções para exclusão de administradores------------------//
//Função para excluir um administrador
    function delete(){
      session_start();
      if(!isset($_SESSION['administrador'])){
        throw new DeleteException();
      }

      $connection = $this->getConnection();

      $sql = "DELETE
              FROM administradores
              WHERE id = ".$_SESSION['administrador'];

      $result = mysqli_query($connection, "SELECT * FROM administradores");
      $linhas_ant = mysqli_num_rows($result);

      $action = mysqli_query($connection, $sql);
      $result = mysqli_query($connection, "SELECT * FROM administradores");
      $linhas_pos = mysqli_num_rows($result);
      mysqli_close($connection);

      if ($linhas_pos == $linhas_ant - 1) {
        return true;
      }
      else {
        return false;
      }
    }
//Função para exluir todos os administradores
  function deleteAll() {
    session_start();
    if(!isset($_SESSION['administrador'])){
      throw new DeleteException();
    }

    $credentials = $this->get_mysql_credentials();
    $connection = mysqli_connect($credentials['host'], $credentials['login'], $credentials['senha'], $credentials['database']);
    $sql = "DROP TABLE administradores";
    $action = mysqli_query($connection, $sql);
    $sql = "CREATE TABLE administradores (id INT NOT NULL AUTO_INCREMENT,
								                          login VARCHAR(50) NOT NULL,
								                          senha VARCHAR(50) NOT NULL,
                                          primeiro_nome VARCHAR(50) NOT NULL,
                                          sobrenome VARCHAR(50),
                                          email VARCHAR(100) NOT NULL,
                                          PRIMARY KEY(id))";
    $action = mysqli_query($connection, $sql);
    $result = mysqli_query($connection, "SELECT * FROM administradores");
    $linhas = mysqli_num_rows($result);
    mysqli_close($connection);
    if($linhas == 0){
      return true;
    }
    else return false;
  }

//-------------------Métodos para recuperar administradores-------------------//
//Retorna um administrador específico
  function getById($id){
    $connection = $this->getConnection();

    $sql = 'SELECT *
            FROM administradores
            WHERE id = '.$id;

    $resultado = mysqli_query($connection, $sql);
    $linhas = mysqli_num_rows($resultado);

    if($linhas == 0){
      mysqli_close($connection);
      throw new GetUserException();
    }
    else{
      $data = mysqli_fetch_assoc($resultado);
      mysqli_close($connection);
      $administrador = new Administrador($data);
      $administrador->setId($data['id']);
      return $administrador;
    }
  }
//Retorna um administrador logado
  function getSelf(){
    session_start();
    $id = $_SESSION['administrador'];
    $connection = $this->getConnection();

    $sql = 'SELECT *
            FROM administradores
            WHERE id = '.$id;

    $resultado = mysqli_query($connection, $sql);
    $linhas = mysqli_num_rows($resultado);

    if($linhas != 1){
      mysqli_close($connection);
      throw new GetUserException();
    }
    else{
      $data = mysqli_fetch_assoc($resultado);
      mysqli_close($connection);
      $administrador = new Administrador($data);
      $administrador->setId($data['id']);
      var_dump($administrador);
      return $administrador;
    }
  }
//Retorna todos os administradores
  function getAll(){
    $administradores = [];
    $connection = $this->getConnection();
    $sql = 'SELECT * FROM administradores';
    $resultado = mysqli_query($connection, $sql);
    $linhas = mysqli_num_rows($resultado);
    for ($i=0; $i < $linhas; $i++) {
      $administrador = mysqli_fetch_assoc($resultado);
      $administradores[$i] = new Administrador($administrador);
      $administradores[$i]->setId($administrador['id']);
    }
    mysqli_close($connection);
    return $administradores;
  }
//---------------------------------Auxiliares---------------------------------//
//Busca as credênciais para o banco de dados
  function get_mysql_credentials(){
    $file = fopen("../private/credentials.json", "r");
    $jsonStr = '';

    while(!feof($file)){
        $jsonStr .= fgets($file);
    }

    $credentials = json_decode($jsonStr, true);
    return $credentials[0];
  }
//Retorna uma conxão mysql
  function getConnection(){
    $credentials = $this->get_mysql_credentials();
    $connection = mysqli_connect($credentials['host'], $credentials['login'], $credentials['senha'], $credentials['database']);
    return $connection;
  }
//Exclui todas as pastas e arquivos de um diretório
  function cleanDirectory($directory){
      foreach(glob("{$directory}/*") as $file)
      {
          if(is_dir($file)) {
              cleanDirectory($file);
              rmdir($file);
          } else {
              unlink($file);
          }
      }
  }
//Função para validação de emails
  function validateEmail($email){
    return filter_var($email, FILTER_VALIDATE_EMAIL);
  }
//Retorna a quantidade usuários cadastrados
  function getNumDeUsuarios(){
    $credentials = $this->get_mysql_credentials();
    $connection = mysqli_connect($credentials['host'], $credentials['login'], $credentials['senha'], $credentials['database']);
    $sql = 'SELECT * FROM administradores';
    $resultado = mysqli_query($conexao, $sql);
    $numUsuarios = mysqli_num_rows($resultado);
    mysqli_close($connection);
    return $numUsuarios;
  }
}
?>
