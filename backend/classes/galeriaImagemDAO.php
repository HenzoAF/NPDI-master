<?php
class galeriaImagemDAO{

  private function __construct(){
  }

  function __destruct(){
  }

//--------------------Função que instancia a classe DAO-----------------------//
  public static function getInstance() {
    static $instance = null;
    if (null === $instance) {
        $instance = new static();
    }
    return $instance;
  }

//-------------------Função para cadastrar novo uma nova imagem-----------------------//
//Registra a imagem no banco de dados
  public function insert($array, $file){

    session_start();

    //Verificando requisitos da imagem
    $upload = true;
    $extensao = pathinfo($file["name"],PATHINFO_EXTENSION);
    if ($file["size"] > 5120000) {
       $upload = false;
    }
    if($extensao != "jpg" && $extensao != "png" && $extensao != "jpeg" && $extensao != "bmp" && $extensao != "svg") {
        $upload = false;
    }
    if (!$upload) {
      throw new InsertionException();

    }

    //Conectando ao db
    $credentials = $this->get_mysql_credentials();
    $connection = mysqli_connect($credentials['host'], $credentials['login'], $credentials['senha'], $credentials['database']);
    $resultado = mysqli_query($connection, "SELECT * FROM galeriaImagens");
    $linhas_ant = mysqli_num_rows($resultado);

    //Organizando imagem no servidor
    $nomeFinal = "../../frontend/img/galeria/".$linhas_ant.".".$extensao;
    move_uploaded_file($file['tmp_name'], $nomeFinal);
    $img = fopen($nomeFinal, "r") or die("Imagem não inserida corretamente");
    fclose($img);

    //Instanciando objeto
    $novaImagem = new galeriaImagem($array);
    $novaImagem->setCaminho($nomeFinal)
    $novaImagem->setAdministradores_id($_SESSION['administrador']);

    $sql = "INSERT INTO galeriaImagens (administradores_id, categorias_id, titulo, caminho)
            VALUES (".$novaImagem->getAdministradores_id().", '".$novaImagem->getCategorias_id()."', '".$novaImagem->getTitulo()."', '".$novaImagem->getCaminho()."')";

    $action = mysqli_query($connection, $sql);
    $resultado = mysqli_query($connection, "SELECT * FROM galeriaImagens");
    $linhas_pos = mysqli_num_rows($resultado);
    mysqli_close($connection);
    if($linhas_pos = $linhas_ant + 1){
      return $novaImagem;
    }
    else throw new InsertionException();

  }

//---------------------Função para excluir uma imagem----------------------------//
  public function delete($id){
    session_start();
    $credentials = $this->get_mysql_credentials();
    $connection = mysqli_connect($credentials['host'], $credentials['login'], $credentials['senha'], $credentials['database']);

    $sql = "SELECT *
            FROM galeriaImagens
            WHERE id = ".$id;
    $resultado = mysqli_query($connection, $sql);
    $imagem = mysqli_fetch_assoc($resultado);
    if($imagem['administradores_id'] != $_SESSION['administrador']){
      throw new DeleteException();
    }
    unlink($imagem['caminho']);

    $resultado = mysqli_query($connection, "SELECT * FROM galeriaImagens");
    $linhas_ant = mysqli_num_rows($resultado);

    $sql = "DELETE
            FROM galeriaImagens
            WHERE id = ".$id;

    $action = mysqli_query($connection, $sql);
    $resultado = mysqli_query($connection, "SELECT * FROM galeriaImagens");
    $linhas_pos = mysqli_num_rows($resultado);
    mysqli_close($connection);

    if($linhas_pos == $linhas_ant - 1){
      return true;
    }
    else return false;
  }
//-------------------Funções para recuperar imagens-----------------------------//
//Recuperar imagem pelo id
  public function getById($id){
    $credentials = $this->get_mysql_credentials();
    $connection = mysqli_connect($credentials['host'], $credentials['login'], $credentials['senha'], $credentials['database']);

    $sql = "SELECT *
            FROM galeriaImagens
            WHERE id = ".$id;

    $result = mysqli_query($connection, $sql);
    $linhas = mysqli_num_rows($result);

    if ($linhas == 0) {
      mysqli_close($connection);
      throw new GetImageException();
    }
    else {
      $assoc = mysqli_fetch_assoc($result);
      $array = array( 'id'=>$assoc['id'],
                      'administradores_id' => $assoc['administradores_id'],
                      'categorias_id' => $assoc['categorias_id'],
                      'titulo' => $assoc['titulo'],
                      'caminho' => $assoc['caminho']);
      $imagem = new Imagem($array);
      $imagem->setId($array['id']);
      $imagem->setCaminho($array['caminho']);
      $imagem->setAdministradores_id($array['administradores_id']);
      mysqli_close($connection);
      $img = fopen($imagem->getCaminho(), "r") or die("Imagem não recuperada corretamente");
      fclose($img);
      return $imagem;
    }
  }

//Recuperar imagem pelo id do autor
  public function getByAdmin($id){
    $imagem = [];
    $credentials = $this->get_mysql_credentials();
    $connection = mysqli_connect($credentials['host'], $credentials['login'], $credentials['senha'], $credentials['database']);

    $sql = "SELECT *
            FROM galeriaImagens
            WHERE administradores_id = ".$id;

    $result = mysqli_query($connection, $sql);
    $linhas = mysqli_num_rows($result);

    if ($linhas == 0) {
      mysqli_close($connection);
      throw new GetImageException();
    }
    else {
      $assoc = mysqli_fetch_assoc($resultado);
      for ($i=0; $i < $linhas; $i++) {
        $array = array( 'id'=>$assoc['id'],
                        'administradores_id' => $assoc['administradores_id'],
                        'categorias_id' => $assoc['categorias_id'],
                        'titulo' => $assoc['titulo'],
                        'caminho' => $assoc['caminho']);
        $imagem[$i] = new Imagem($array);
        $imagem[$i]->setId($array['id']);
        $imagem[$i]->setCaminho($array['caminho']);
        $imagem[$i]->setAdministradores_id($array['administradores_id']);
        $img = fopen($imagem[$i]->getCaminho(), "r") or die("Imagem não recuperada corretamente");
        fclose($img);
      }
      mysqli_close($connection);
      return $imagem;
    }
  }
//Recuperar imagem por categoria
  public function getByCategory($id){
    $imagem = [];
    $credentials = $this->get_mysql_credentials();
    $connection = mysqli_connect($credentials['host'], $credentials['login'], $credentials['senha'], $credentials['database']);

    $sql = "SELECT *
            FROM galeriaImagens
            WHERE categorias_id = ".$id;

    $result = mysqli_query($connection, $sql);
    $linhas = mysqli_num_rows($result);

    if ($linhas == 0) {
      mysqli_close($connection);
      throw new GetImageException();
    }
    else {
      $assoc = mysqli_fetch_assoc($resultado);
      for ($i=0; $i < $linhas; $i++) {
        $array = array( 'id'=>$assoc['id'],
                        'administradores_id' => $assoc['administradores_id'],
                        'categorias_id' => $assoc['categorias_id'],
                        'titulo' => $assoc['titulo'],
                        'caminho' => $assoc['caminho']);
        $imagem[$i] = new Imagem($array);
        $imagem[$i]->setId($array['id']);
        $imagem[$i]->setCaminho($array['caminho']);
        $imagem[$i]->setAdministradores_id($array['administradores_id']);
        $img = fopen($imagem[$i]->getCaminho(), "r") or die("Imagem não recuperada corretamente");
        fclose($img);
      }
      mysqli_close($connection);
      return $imagem;
    }
  }

//-------------------------------Auxiliares-----------------------------------//
  function get_mysql_credentials(){
    $file = fopen("../private/credentials.json", "r");
    $jsonStr = '';

    while(!feof($file)){
        $jsonStr .= fgets($file);
    }

    $credentials = json_decode($jsonStr, true);
    return $credentials[0];
  }
  function cleanDirectory($directory){
      foreach(glob("{$directory}/*") as $file)
      {
          if(is_dir($file)) {
              cleanDirectory($file);
              rmdir($file);
          }
          else {
              unlink($file);
          }
      }
  }
}
?>
