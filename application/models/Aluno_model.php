<?php

class Aluno_model extends CI_Model {

    public function inserir_aluno($aluno)
    {
        return $this->db->insert("aluno", $aluno);
    }

    public function atualizar_aluno($aluno, $id_aluno)
    {
        return $this->db->where("id_aluno", $id_aluno)
                        ->update("aluno", $aluno);
    }

    public function buscar_alunos($id_aluno = NULL)
    {
        if (!empty($id_aluno))
        {
            $this->db->where("id_aluno", $id_aluno);
        }
        return $this->db->order_by("nome")
                        ->get("aluno");
    }

    public function deletar_aluno($id_aluno)
    {
        return $this->db->where("id_aluno", $id_aluno)
                        ->delete("aluno");
    }

}
