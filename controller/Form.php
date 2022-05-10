<?php
class Form
{
  private $message = "";
  private $error = "";
  public function __construct()
  {
    Transaction::open();
  }
  public function controller()
  {
    $form = new Template("view/form.html");
    $form->set("id", "");
    $form->set("modelo", "");
    $form->set("processador", "");
    $form->set("sistemaoperacional", "");
    $this->message = $form->saida();
  }
  public function salvar()
  {
    if (isset($_POST["modelo"]) && isset($_POST["processador"]) && isset($_POST["sistemaoperacional"])) {
      try {
        $conexao = Transaction::get();
        $tvbox = new Crud("tvbox");
        $modelo = $conexao->quote($_POST["modelo"]);
        $processador = $conexao->quote($_POST["processador"]);
        $sistemaoperacional = $conexao->quote($_POST["sistemaoperacional"]);
        if (empty($_POST["id"])) {
          $tvbox->insert(
            "modelo, processador, sistemaoperacional",
            "$modelo, $processador, $sistemaoperacional"
          );
        } else {
          $id = $conexao->quote($_POST["id"]);
          $tvbox->update(
            "modelo = $modelo, processador = $processador, sistemaoperacional = $sistemaoperacional",
            "id = $id"
          );
        }
        $this->message = $tvbox->getMessage();
        $this->error = $tvbox->getError();
      } catch (Exception $e) {
        $this->message = $e->getMessage();
        $this->error = true;
      }
    } else {
      $this->message = "Campos não informados!";
      $this->error = true;
    }
  }
  public function editar()
  {
    if (isset($_GET["id"])) {
      try {
        $conexao = Transaction::get();
        $id = $conexao->quote($_GET["id"]);
        $tvbox = new Crud("tvbox");
        $resultado = $tvbox->select("*", "id = $id");
        if (!$tvbox->getError()) {
          $form = new Template("view/form.html");
          foreach ($resultado[0] as $cod => $sistemaoperacional) {
            $form->set($cod, $sistemaoperacional);
          }
          $this->message = $form->saida();
        } else {
          $this->message = $tvbox->getMessage();
          $this->error = true;
        }
      } catch (Exception $e) {
        $this->message = $e->getMessage();
        $this->error = true;
      }
    }
  }
  public function getMessage()
  {
    if (is_string($this->error)) {
      return $this->message;
    } else {
      $msg = new Template("view/msg.html");
      if ($this->error) {
        $msg->set("cor", "danger");
      } else {
        $msg->set("cor", "success");
      }
      $msg->set("msg", $this->message);
      $msg->set("uri", "?class=Tabela");
      return $msg->saida();
    }
  }
  public function __destruct()
  {
    Transaction::close();
  }
}