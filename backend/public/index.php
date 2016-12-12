<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

// Carregamento dos arquivos fonte do projeto
spl_autoload_register(function ($classname){
    require ("../classes/" . $classname . ".php");
});

$app = new \Slim\App;
header("Access-Control-Allow-Origin: http://localhost:4200");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//--------------------- Rotas para administradores ---------------------------//
// Rota para cadastro de administrador
$app->post('/cadastro', function (Request $request, Response $response){
  $data = $request->getParsedBody();
  $administradorDAO = AdministradorDAO::getInstance();
  try {
    $novoAdministrador = $administradorDAO->insert($data);
  } catch (InsertionException $e) {
    return $response->withStatus(406);
  }
  return $response->withJson($novoAdministrador);
});
//Rota para upload de imagem do administrador
$app->post('/uploadImage', function(Request $request, Response $response){
  var_dump($_FILES);
  $administradorDAO = AdministradorDAO::getInstance();
  try {
    $administradorDAO->uploadImage($_FILES['imagem']);
  } catch (InsertionException $e) {
    return $response->withStatus(406);
  }
});
// Rota para deletar administrador
$app->delete('/delete', function(Request $request, Response $response){
  $administradorDAO = AdministradorDAO::getInstance();
  try {
    $administradorDAO->delete();
  } catch (DeleteException $e) {
    return $response->withStatus(404);
  }
});
// Rota para login de administrador
$app->post('/login', function(Request $request, Response $response){
  $data = $request->getParsedBody();
  $administradorDAO = AdministradorDAO::getInstance();
  try {
    $administrador = $administradorDAO->validate($data['login'], $data['senha']);
  }
  catch (ValidateException $e) {
    return $response->withStatus(403);
  }
  return $response->withJson($administrador);
});
// Rota para logout
$app->get('/logout', function(Request $request, Response $response){
  $administradorDAO = AdministradorDAO::getInstance();
  try {
    $administradorDAO->logout();
  } catch (LogoutException $e) {
    return $response->withStatus(412);
  }

});
// Rota para receber objeto de um administrador
$app->get('/home', function(Request $request, Response $response){
  $administradorDAO = AdministradorDAO::getInstance();
  try {
    $administrador = $administradorDAO->getSelf();
  } catch (GetUserException $e) {
    return $response->withStatus(404);
  }
  return $response->withJson($administrador);
});
//Rota para reber um administrador pelo id
$app->get('/get/{id}', function(Request $request, Response $response, $args){
  $id = $args['id'];
  $administradorDAO = AdministradorDAO::getInstance();
  try {
    $administrador = $administradorDAO->getById($id);
  } catch (GetUserException $e) {
    return $response->withStatus(404);
  }
  return $response->withJson($administrador);
});
//Rota para receber todos os administradores
$app->get('/administradores', function(Request $request, Response $response){
  $data = $request->getParsedBody();
  $administradorDAO = AdministradorDAO::getInstance();
  $administradores = $administradorDAO->getAll();
  var_dump($administradores);
  return $response->withJson($administradores);
});
//------------------------- Rotas para postagens -----------------------------//
//Rota para cadastrar um novo post
$app->post('/novoPost', function(Request $request, Response $response){
  $data = $request->getParsedBody();
  $postDAO = PostDAO::getInstance();
  try {
    $postDAO->insert($data);
  } catch (DeleteException $e) {
    return $response->withStatus(404);
  }

});
//------------------------- Rotas para galeria -------------------------------//
//Essas não funcionam ainda...
//Rota para adicionar categoria
$app->post('/novaCategoria', function(Request $request, Response $response){
  $data = $request->getParsedBody();
  $categoriasDAO = CategoriaDAO::getInstance();
  try {
    $categoriasDAO->insert($data);
  } catch (CategoryException $e) {
    return $response->withStatus(400);
  }
});
//Rota para excluir categoria
$app->delete('/excluirCategoria', function(Request $request, Response $response){
  $data = $request->getParsedBody();
  $categoriaDAO = CategoriaDAO::getInstance();
  try {
    $categoriaDAO->delete($data);
  } catch (DeleteException $e) {
    return $response->withStatus(400);
  }
});
//Rota para adicionar imagens a galeria
$app->post('/uploadImagem', function(Request $request, Response $response){
  $data = $request->getParsedBody();
  $imagemDAO = ImagemDAO::getInstance();
  try {
    $imagemDAO->insert($data, $_FILES["fileToUpload"]);
  } catch (InsertionException $e) {
    return $response->withStatus(400);
  }

});
//Rota para recuperar uma imagem
$app->post('/imagem', function(Request $request, Response $response){
  $data = $request->getParsedBody();
  $imagemDAO = ImagemDAO::getInstance();
  try {
    $imagemDAO->getById($data['id']);
  } catch (GetImageException $e) {
    return $response->withStatus(404);
  }

});
//Recuoperar todas as imagens de uma galeria
$app->post('/categoria', function(Request $request, Response $response){
  $data = $request->getParsedBody();
  $imagemDAO = ImagemDAO::getInstance();
  try {
    $imagemDAO->getByCategory($data['id']);
  } catch (GetImageException $e) {
    return $response->withStatus(404);
  }

});
//Rota para deletar uma imagem
$app->delete('deletarImagem', function(Resquest $request, Response $response){
  $data = $request->getParsedBody();
  $imagemDAO = ImagemDAO::getInstance();
  try {
    $imagemDAO->delete($data);
  } catch (DeleteException $e) {
    return $response->withStatus(400);
  }
});

$app->run();
