<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH . 'third_party/REST_Controller.php';
require APPPATH . 'third_party/Format.php';

use Restserver\Libraries\REST_Controller;

class Api extends REST_Controller {

    public function __construct()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT ");

        parent::__construct();

        $this->load->model("Aluno_model", "aluno");
    }

    private function _validar_aluno($aluno)
    {
        return !empty($aluno['nome']) &&
                !empty($aluno['logradouro']) &&
                !empty($aluno['numero']) &&
                !empty($aluno['bairro']) &&
                !empty($aluno['cidade']) &&
                !empty($aluno['estado']) &&
                strlen($aluno['estado']) <= 2 &&
                !empty($aluno['cep']);
    }

    private function _incluir_aluno($dados)
    {
        $inserido = $this->aluno->inserir_aluno($dados);

        if ($inserido)
        {
            $status = parent::HTTP_OK;
            $msg    = "Aluno incluído com sucesso";
        }
        else
        {
            $status = parent::HTTP_INTERNAL_ERROR;
            $msg    = "Aluno não pôde ser incluído";
        }

        return ["status" => $status, "resposta" => $msg];
    }

    private function _editar_aluno($dados, $id_aluno)
    {
        if ($this->aluno->atualizar_aluno($dados, $id_aluno))
        {
            $status = parent::HTTP_OK;
            $msg    = "Aluno editado com sucesso";
        }
        else
        {
            $status = parent::HTTP_INTERNAL_ERROR;
            $msg    = "Aluno não pôde ser editado";
        }

        return ["status" => $status, "resposta" => $msg];
    }

    private function _upload_imagem($dados = NULL)
    {
        $nome_arquivo = "aluno_" . $dados['nome'] . "_" . $dados['cidade'];

        $config['upload_path']   = './uploads/';
        $config['allowed_types'] = 'jpg|png';
        $config['max_size']      = '1536';
        $config['max_width']     = '1024';
        $config['max_height']    = '1024';
        $config['overwrite']     = TRUE;
        $config['file_name']     = mb_strtolower(preg_replace("/[^A-Za-z0-9\.\-]/", "", $nome_arquivo));

        $this->load->library('upload', $config);

        if (!$this->upload->do_upload('imagem'))
        {
            $status = parent::HTTP_BAD_REQUEST;
            $this->response($this->upload->display_errors(NULL, NULL), $status);
            exit();
        }
        else
        {
            $data = array('upload_data' => $this->upload->data());
            return $data;
        }
    }

    public function aluno_post($id_aluno = NULL)
    {
        $dados = array(
            'nome' => $this->input->post("nome"),
            'logradouro' => $this->input->post("logradouro"),
            'numero' => $this->input->post("numero"),
            'complemento' => $this->input->post("complemento"),
            'bairro' => $this->input->post("bairro"),
            'cidade' => $this->input->post("cidade"),
            'estado' => $this->input->post("estado"),
            'cep' => $this->input->post("cep")
        );

        if ($this->_validar_aluno($dados))
        {
            if (!empty($_FILES['imagem']))
            {
                $imagem          = $this->_upload_imagem($dados);
                $dados['imagem'] = base_url("/uploads/") . $imagem['upload_data']['file_name'];
            }

            if (!empty($id_aluno))
            {
                $resultado = $this->_editar_aluno($dados, $id_aluno);
            }
            else
            {
                $resultado = $this->_incluir_aluno($dados);
            }
            $this->response($resultado['resposta'], $resultado['status']);
        }
        else
        {
            $status   = parent::HTTP_OK;
            $resposta = "Os dados do aluno não são válidos";
            $this->response($resposta, $status);
        }
    }

    public function alunos_get($id_aluno = NULL)
    {
        if (!empty($id_aluno))
        {
            $dados = $this->aluno->buscar_alunos($id_aluno)->row();
        }
        else
        {
            $dados = $this->aluno->buscar_alunos()->result();
        }

        $status   = parent::HTTP_OK;
        $resposta = ['status' => $status, 'dados' => $dados];
        $this->response($resposta, $status);
    }

    public function aluno_delete($id_aluno = NULL)
    {
        if (!empty($id_aluno))
        {
            if ($this->aluno->deletar_aluno($id_aluno))
            {
                $status = parent::HTTP_OK;
                $msg    = "Aluno excluído com sucesso";
            }
            else
            {
                $status = parent::HTTP_INTERNAL_ERROR;
                $msg    = "Aluno não pôde ser excluído";
            }
        }
        else
        {
            $status = parent::HTTP_BAD_REQUEST;
            $msg    = "O ID do aluno não pode ser nulo";
        }
        $this->response($msg, $status);
    }

}
