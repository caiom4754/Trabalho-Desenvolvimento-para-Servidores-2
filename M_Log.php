<?php

class M_Log extends CI_Model{
    public function inserirlog($usuario, $comando){
        try{
            //instancia do dblog
            $dblog= $this -> load -> database('log', TRUE);

            //chamada a função helper para nos auxiliar
            $comando = trocaCaractere($comando);

            //query de inserção dos dados
            $dblog->query("insert into log (usuario, comando)
                            values ('$usuario', '$comando')");
            
            //verificar se a inserçãi ocorreu com sucesso

            if($dblog->affected_rows() > 0){
                $dados = array('codigo' => 1,
                                'msg' => 'Log cadastrado corretamente');
            }
            else{
                $dados = array('codigo' => 6,
                                'msg' => 'houve algum problema com a inserção do log');
            }

            //fechando a conexão com o banco de log
            $dblog->close();
        }
        catch (Exception $e){
            $dados = array('codigo' => 00,
                            'msg' => 'ATENÇÃO: o seguinte erro aconteceu -> ',
                            $e -> getMessage(), "\n");
        } return $dados;
    }
}
