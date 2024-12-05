<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Produtos extends CI_Controller
{ // Atributos privados da classe
    private $codigo;
    private $descricao;
    private $unid_medida; // Adicionando o atributo unid_medida
    private $estoq_minimo; // Atributo para o estoque mínimo
    private $estoq_maximo; // Atributo para o estoque máximo
    private $dtcria; // Data de criação
    private $usuariologin; // Usuário que criou o produto

    // Getters dos atributos
    public function getCodigo()
    {
        return $this->codigo;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function getUnidMedida()
    {
        return $this->unid_medida;
    }

    public function getEstoq_minimo()
    {
        return $this->estoq_minimo;
    }

    public function getEstoq_maximo()
    {
        return $this->estoq_maximo;
    }

    public function getDtcria()
    {
        return $this->dtcria;
    }

    public function getUsuariologin()
    {
        return $this->usuariologin;
    }

    // Setters dos atributos
    public function setCodigo($codigoFront)
    {
        $this->codigo = $codigoFront;
    }

    public function setDescricao($descricaoFront)
    {
        $this->descricao = $descricaoFront;
    }

    public function setUnidMedida($unidMedidaFront)
    {
        $this->unid_medida = $unidMedidaFront;
    }

    public function setestoq_minimo($estoq_minimoFront)
    {
        $this->estoq_minimo = $estoq_minimoFront;
    }

    public function setEstoq_maximo($estoq_maximoFront)
    {
        $this->estoq_maximo = $estoq_maximoFront;
    }

    public function setDtcria($dtcriaFront)
    {
        $this->dtcria = $dtcriaFront;
    }

    public function setusuariologin($usuariologinFront)
    {
        $this->usuariologin = $usuariologinFront;
    }

    public function inserir()
    {
        // Retornos possíveis:
        // 1 - Produto cadastrado corretamente (Banco)
        // 2 - Faltou informar a descrição (FrontEnd)
        // 3 - Faltou informar a unidade de medida (FrontEnd)
        // 4 - Faltou informar o estoque mínimo (FrontEnd)
        // 5 - Faltou informar o estoque máximo (FrontEnd)
        // 6 - Houve algum problema no insert da tabela (Banco)
        // 7 - Usuario do sistema não informado (front)
        // 8 - Houve problema no salvamento do log, mas o produto foi inserido

        try {
            // Dados recebidos via JSON
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            // Array com os dados que deverão vir do Front
            $lista = array(
                "descricao" => '0',
                "unid_medida" => '0',
                "estoq_minimo" => '0',
                "estoq_maximo" => '0',
                "usuariologin" => '0'
            );

            // Verificando os parâmetros recebidos
            if (verificarParam($resultado, $lista) == 1) {
                // Atribuindo os valores recebidos
                $descricao = $resultado->descricao;
                $unid_medida = $resultado->unid_medida;
                $estoq_minimo = $resultado->estoq_minimo;
                $estoq_maximo = $resultado->estoq_maximo;
                $usuarioLogin = $resultado->usuariologin;

                // Realizando as validações
                if (trim($descricao) == '') {
                    $retorno = array(
                        'codigo' => 2,
                        'msg' => 'Descrição do produto não informada'
                    );
                } elseif (trim($unid_medida) == '') {
                    $retorno = array(
                        'codigo' => 3,
                        'msg' => 'Unidade de medida não informada'
                    );
                } elseif (trim($estoq_minimo) == '') {
                    $retorno = array(
                        'codigo' => 4,
                        'msg' => 'Estoque mínimo não informado'
                    );
                } elseif (trim($estoq_maximo) == '') {
                    $retorno = array(
                        'codigo' => 5,
                        'msg' => 'Estoque máximo não informado'
                    );
                } elseif (trim($usuarioLogin) == '') {
                    $retorno = array(
                        'codigo' => 7,
                        'msg' => 'Usuário do sistema não informado'
                    );
                } else {
                    // Realizando a inserção no banco de dados
                    $this->load->model('M_produto');
                    $retorno = $this->M_produto->inserir(
                        $descricao,
                        $unid_medida,
                        $estoq_minimo,
                        $estoq_maximo,
                        $usuarioLogin
                    );
                }
            } else {
                $retorno = array(
                    'codigo' => 99,
                    'msg' => 'Os campos vindos do FrontEnd não representam o método de inserção. Verifique.'
                );
            }
        } catch (Exception $e) {
            $retorno = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Retorno no formato JSON
        echo json_encode($retorno);
    }

    public function consultar()
    {
        /* retornos:
        1 - produto encontrado (banco)
        2 - preço não pode ser negativo (front-end)
        3 - quantidade do produto não pode ser negativa
        4 - unidade de medida não informada
        6 - dados não encontrados (banco)
        7 - usuario não encontrado ou não definido
        99 - erro inesperado no sistema*/

        try {
            // recebe os parâmetros em json
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            // array com os dados que devem vir do front
            $lista = array(
                "codigo" => '0',
                "descricao" => '0',
                "unid_medida" => '0',
                "estoq_minimo" => '0',
                "estoq_maximo" => '0',
                "dtcria" => '0',
                "usuariologin" => '0'
            );

            if (verificarParam($resultado, $lista) == 1) {
                //fazendo os setters
                $this->setCodigo($resultado->codigo);  // Alterado para 'codigo'
                $this->setDescricao($resultado->descricao);  // Alterado para 'descricao'
                $this->setUnidMedida($resultado->unid_medida);  // Alterado para 'unid_medida'
                $this->setEstoq_minimo($resultado->estoq_minimo);  // Alterado para 'estoq_minimo'
                $this->setEstoq_maximo($resultado->estoq_maximo);  // Alterado para 'estoq_maximo'
                $this->setDtcria($resultado->dtcria);  // Alterado para 'dtcria'
                $this->setusuariologin($resultado->usuariologin);  // Alterado para 'usuariologin'

                // Validações
                if ($this->getDescricao() == '') {
                    $retorno = array(
                        'codigo' => 4,
                        'msg' => 'Descrição não informada'
                    );
                } elseif (trim($this->getUnidMedida()) == '') { // nova validação para unidade de medida
                    $retorno = array(
                        'codigo' => 4,
                        'msg' => 'Unidade de medida não informada'
                    );
                } elseif ($this->getEstoq_minimo() < 0) {
                    $retorno = array(
                        'codigo' => 3,
                        'msg' => 'Estoque mínimo não pode ser negativo'
                    );
                } elseif ($this->getEstoq_maximo() < 0) {
                    $retorno = array(
                        'codigo' => 3,
                        'msg' => 'Estoque máximo não pode ser negativo'
                    );
                } elseif (trim($this->getUsuariologin()) == '') {
                    $retorno = array(
                        'codigo' => 7,
                        'msg' => 'Usuário não informado'
                    );
                } else {
                    // realizando a instância da model
                    $this->load->model('M_produto');

                    // atributo retorno recebe array com informações da consulta
                    $retorno = $this->M_produto->consultar(
                        $this->getCodigo(),
                        $this->getDescricao(),
                        $this->getUnidMedida(),
                        $this->getestoq_minimo(),
                        $this->getEstoq_maximo(),
                        $this->getDtcria(),
                        $this->getusuariologin() // passando todos os parâmetros para a consulta
                    );
                }
            } else {
                $retorno = array(
                    'codigo' => 99,
                    'msg' => 'Parâmetros inválidos'
                );
            }
        } catch (Exception $e) {
            $retorno = array(
                'codigo' => 99,
                'msg' => 'O seguinte erro ocorreu: ' . $e->getMessage()
            );
        }
        echo json_encode($retorno);
    }

    public function alterar()
    {
        /* retornos:
        1 - sucesso
        2 - código não informado
        3 - preço não pode ser negativo
        4 - quantidade não pode ser negativa
        5 - nome ou descrição não informado
        6 - dados não encontrados
        7 - usuário não informado
        8 - unidade de medida não informada */

        try {
            // Recebe os dados enviados no corpo da requisição
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            // Lista de parâmetros que devem ser recebidos
            $lista = array(
                "codigo" => '0',  // Código do produto
                "descricao" => '0',  // Descrição do produto
                "unid_medida" => '0',  // Unidade de medida
                "estoq_minimo" => '0',  // Estoque mínimo
                "estoq_maximo" => '0',  // Estoque máximo
                "dtcria" => '0',  // Data de criação
                "usuariologin" => '0'  // Usuário que criou
            );

            // Verifica se os parâmetros estão corretos
            if (verificarParam($resultado, $lista) == 1) {
                // Fazendo os setters com os dados recebidos
                $this->setCodigo($resultado->codigo);  // Alterado para 'codigo'
                $this->setDescricao($resultado->descricao);  // Alterado para 'descricao'
                $this->setUnidMedida($resultado->unid_medida);  // Alterado para 'unid_medida'
                $this->setestoq_minimo($resultado->estoq_minimo);  // Alterado para 'estoq_minimo'
                $this->setEstoq_maximo($resultado->estoq_maximo);  // Alterado para 'estoq_maximo'
                $this->setDtcria($resultado->dtcria);  // Alterado para 'dtcria'
                $this->setusuariologin($resultado->usuariologin);  // Alterado para 'usuariologin'

                // Validações
                if (trim($this->getCodigo()) == '' || trim($this->getCodigo()) == '0') {
                    $retorno = array(
                        'codigo' => 2,
                        'msg' => 'Código não informado'
                    );
                } elseif ($this->getestoq_minimo() < 0) {
                    $retorno = array(
                        'codigo' => 4,
                        'msg' => 'Estoque mínimo não pode ser negativo'
                    );
                } elseif ($this->getEstoq_maximo() < 0) {
                    $retorno = array(
                        'codigo' => 4,
                        'msg' => 'Estoque máximo não pode ser negativo'
                    );
                } elseif (trim($this->getDescricao()) == '') {
                    $retorno = array(
                        'codigo' => 5,
                        'msg' => 'Descrição não informada'
                    );
                } elseif (trim($this->getUnidMedida()) == '') { // nova validação para unidade de medida
                    $retorno = array(
                        'codigo' => 8,
                        'msg' => 'Unidade de medida não informada'
                    );
                } elseif (trim($this->getusuariologin()) == '') {
                    $retorno = array(
                        'codigo' => 7,
                        'msg' => 'Usuário não informado'
                    );
                } else {
                    // Realiza a consulta de alteração na model
                    $this->load->model('M_produto');
                    $retorno = $this->M_produto->alterar(
                        $this->getCodigo(),
                        $this->getDescricao(),
                        $this->getUnidMedida(),
                        $this->getestoq_minimo(),
                        $this->getEstoq_maximo(),
                        $this->getDtcria(),
                        $this->getusuariologin() // Passando todos os parâmetros para a consulta
                    );
                }
            } else {
                $retorno = array(
                    'codigo' => 99,
                    'msg' => 'Os campos recebidos não correspondem ao método de alteração esperado. Verifique.'
                );
            }
        } catch (Exception $e) {
            $retorno = array(
                'codigo' => 0,
                'msg' => 'O seguinte erro ocorreu: ' . $e->getMessage()
            );
        }
        echo json_encode($retorno);
    }

    public function desativar()
    {
        /* retornos:
        1 - produto desativado corretamente
        2 - código do produto não informado
        3 - produto não encontrado
        5 - usuário não informado
        6 - erro no log, mas funfou
        8 - unidade de medida não informada
        */

        try {
            // Recebe os parâmetros via JSON
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            // Define os parâmetros esperados
            $lista = array(
                "codigo" => '0',
                "usuariologin" => '0',
            );

            // Verifica se os parâmetros estão corretos
            if (verificarParam($resultado, $lista) == 1) {
                // Define os setters para os dados recebidos
                $this->setCodigo($resultado->codigo);
                $this->setUsuarioLogin($resultado->usuariologin);

                // Realiza as validações
                if (empty(trim($this->getCodigo())) || $this->getCodigo() == '0') {
                    $retorno = array(
                        "codigo" => 2,
                        "msg" => 'Código do produto não informado.'
                    );
                } elseif (empty(trim($this->getUsuarioLogin()))) {
                    $retorno = array(
                        "codigo" => 5,
                        "msg" => 'Usuário não informado.'
                    );
                } else {
                    // Chama o método para desativar o produto diretamente
                    $this->load->model('M_produto');
                    $retorno = $this->M_produto->desativar(
                        $this->getCodigo(),
                        $this->getUsuariologin()
                    );
                }
            } else {
                $retorno = array(
                    "codigo" => 99,
                    "msg" => 'Os campos recebidos não correspondem ao método de desativação. Verifique.'
                );
            }
        } catch (Exception $e) {
            $retorno = array(
                "codigo" => 0,
                "msg" => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Retorna os dados no formato JSON
        echo json_encode($retorno);
    }
}
