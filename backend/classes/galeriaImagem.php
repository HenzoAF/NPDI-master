<?php
//Classe para os administradores do site
  class galeriaImagem {

//Atributos do administrador
    private $id;
    private $administradores_id;
    private $cetegorias_id;
    private $titulo;
    private $caminho;

//Construtor para a classe
    function __construct(array $data){
      $this->categorias_id = $data["categorias_id"];
      $this->titulo = $data['titulo'];
    }

//Getters & Setters para o id
  public function getId(){
    return $this->id;
  }
  public function setId($id){
    $this->id = $id;
  }
//Getters & Setters para administrador
  public function getAdministradores_id(){
    return $this->administradores_id;
  }
  public function setAdministradores_id($id){
    $this->administradores_id = $id;
  }
//Getters & Setters para categoria
  public function getCategorias_id(){
    return $this->categorias_id;
  }
  public function setCategorias_id($id){
    $this->categorias_id = $id;
  }
//Get para titulo
  public function getTitulo(){
    return $this->titulo;
  }
//Get para caminho
  public function getCaminho(){
    return $this->caminho;
  }
  public function setCaminho($caminho){
    $this->caminho = $caminho;
  }

}
?>
