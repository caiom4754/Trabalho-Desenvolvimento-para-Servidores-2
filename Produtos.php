<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Produtos extends CI_Controller
{
    //Atributos para produtos, vou tentar adicionar pelo menos 5
    private $codigo_produto;
    private $nome_produto;
    private $preco_produto;
    private $descricao_produto;
    private $quantidade_produto;
    private $usuariologin;

    //Getters
    public function getCodigoProduto()
    {
        return $this->codigo_produto;
    }
    public function getNomeProduto()
    {
        return $this->nome_produto;
    }
    public function getPrecoProduto()
    {
        return $this->preco_produto;
    }
    public function getDescricaoProduto()
    {
        return $this->descricao_produto;
    }
    public function getQuantidadeProduto()
    {
        return $this->quantidade_produto;
    }
    public function getUsuariologin()
    {
        return $this->usuariologin;
    }

    //Setters
    public function setCodigoProduto($codigo_produto_Front)
    {
        $this->codigo_produto = $codigo_produto_Front;
    }
    public function setNomeProduto($nome_produto_Front)
    {
        $this->nome_produto = $nome_produto_Front;
    }
    public function setPrecoProduto($preco_produto_Front)
    {
        $this->preco_produto = $preco_produto_Front;
    }
    public function setDescricaoProduto($descricao_produto_Front)
    {
        $this->descricao_produto = $descricao_produto_Front;
    }
    public function setQuantidadeProduto($quantidade_produto_Front)
    {
        $this->quantidade_produto_produto = $quantidade_produto_Front;
    }
    public function setUsuarioLogin($usuariologinFront)
    {
        $this->usuariologin = $usuariologinFront;
    }

    public function inserir()
    {
        // retornos possíveis:
        // 1 - produto cadastrado corretamente (banco)
        // 2 - faltou informar o código do produto (front-end)
        // 3 - codigo do produdo maior que 5 caracteres (front-end)
        // 4 - faltou informar o nome do produto (front-end)
        // 5 - faltou informar o preço do produto (front-end)
        // 5.5 - preço não pode ser menor ou igual a 0 (zero) (front)
        // 6 - faltou informar a descrição do produto (front-end)
        // 7 - faltou informar a quantidade do produto (front-end)
        // 7.7 - quantidade não pode ser menor ou igual a 0 (zero) (front)
        // 8 - houve algum problema no insert da tabela (banco)
        // 9 - houve problema no salvamento do log, mas o produto foi inserido
        // 10 - usuario não informado ou não encontrado
        // 99 - Os campos recebidos do front não estão corretos

        try {
            //dados recebidos via JSON
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            //array com os dados esperados do front
            $lista = array(
                "codigo_produto" => '0',
                "nome_produto" => '0',
                "preco_produto" => '0',
                "descricao_produto" => '0',
                "quantidade_produto" => '0',
                "usuariologin" => '0'
            );

            if (verificarParam($resultado, $lista) == 1) { //dessa vez sem usar -1 kk
                //fazendo os setters
                $this->setCodigoProduto($resultado->codigo_produto);
                $this->setNomeProduto($resultado->nome_produto);
                $this->setPrecoProduto($resultado->preco_produto);
                $this->setDescricaoProduto($resultado->descricao_produto);
                $this->setQuantidadeProduto($resultado->quantidade_produto);
                $this->setUsuarioLogin($resultado->usuariologin);

                //validação dos campos
                if (trim($this->getCodigoProduto()) == '') {
                    $retorno = array(
                        'codigo' => 2,
                        'msg' => 'Codigo do Produto não informado'
                    );
                } elseif (strlen($this->getCodigoProduto()) > 5) {
                    $retorno = array(
                        'codigo' => 3,
                        'msg' => 'Codigo do Produto maior que 5 caracteres'
                    );
                } elseif (trim($this->getNomeProduto()) == '') {
                    $retorno = array(
                        'codigo' => 4,
                        'msg' => 'Nome do Produto não informado'
                    );
                } elseif (trim($this->getPrecoProduto()) == '') {
                    $retorno = array(
                        'codigo' => 5,
                        'msg' => 'Preco do Produto não informado'
                    );
                } elseif ($this->getPrecoProduto() <= 0) {
                    $retorno = array(
                        'codigo' => 5.5,
                        'msg' => 'Preço não pode ser menor ou igual a 0 (zero)'
                    );
                } elseif (trim($this->getDescricaoProduto()) == '') {
                    $retorno = array(
                        'codigo' => 6,
                        'msg' => 'Descrição do Produto não informado'
                    );
                } elseif (trim($this->getQuantidadeProduto()) == '') {
                    $retorno = array(
                        'codigo' => 7,
                        'msg' => 'Quantidade do Produto não informado'
                    );
                } elseif ($this->getQuantidadeProduto() <= 0) {
                    $retorno = array(
                        'codigo' => 7.7,
                        'msg' => 'Quantidade do Produto não não pode ser menor ou igual a 0 (zero)'
                    );
                } elseif (trim($this->getUsuarioLogin()) == '') {
                    $retorno = array(
                        'codigo' => 10,
                        'msg' => 'Usuário não informado'
                    );
                } else {
                    //carrega a model
                    $this->load->model('M_produdos');

                    //inserindo no banco
                    $resultadoInserir = $this->M_produtos->inserir(
                        $this->getCodigoProduto(),
                        $this->getNomeProduto(),
                        $this->getPrecoProduto(),
                        $this->getDescricaoProduto(),
                        $this->getQuantidadeProduto(),
                        $this->getUsuarioLogin()
                    );

                    if ($resultadoInserir) {
                        $retorno = array(
                            'codigo' => 1,
                            'msg' => 'Produto inserido com sucesso'
                        );
                    } else {
                        $retorno = array(
                            'codigo' => 8,
                            'msg' => 'Erro ao inserir produto no banco'
                        );
                    }
                }
            } else {
                $retorno = array(
                    'codigo' => 99,
                    'msg' => 'Os campos do front não estão corretos'
                );
            }
        } catch (Exception $e) {
            $retorno = array(
                'codigo' => 0,
                'msg' => 'Erro ao processar: ',
                $e->getMessage()
            );
        }

        //retorno em formato json
        echo json_encode($retorno);
    }

    public function consultar()
    {
        /* retornos:
        1 - produto encontrado (banco)
        2 - preço não pode ser negativo (front-end)
        3 - quantidade do produto não pode ser negativa
        6 - dados não encontrados (banco)
        7 - usuario não encontrado ou não definido
        99 - erro inesperado no sistema*/

        try {
            // recebe os paremetros em json
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            // array com os dados que deveram vir do front
            $lista = array(
                "codigo_produto" => '0',
                "nome_produto" => '0',
                "preco_produto" => '0',
                "descricao_produto" => '0',
                "quantidade_produto" => '0',
                "usuariologin" => '0'
            );

            if (verificarParam($resultado, $lista) == 1) {
                //fazendo os setters
                $this->setCodigoProduto($resultado->codigo_produto);
                $this->setNomeProduto($resultado->nome_produto);
                $this->setPrecoProduto($resultado->preco_produto);
                $this->setDescricaoProduto($resultado->descricao_produto);
                $this->setQuantidadeProduto($resultado->quantidade_produto);
                $this->setUsuarioLogin($resultado->usuariologin);

                // vaçidações
                if ($this->getPrecoProduto() < 0) {
                    $retorno = array(
                        'codigo' => 2,
                        'msg' => 'Preço não pode ser negativo'
                    );
                } elseif ($this->getQuantidadeProduto() < 0) {
                    $retorno = array(
                        'codigo' => 3,
                        'msg' => 'Quantidade do produto não pode ser negativa'
                    );
                } elseif (trim($this->getUsuarioLogin()) == '') {
                    $retorno = array(
                        'codigo' => 7,
                        'msg' => 'Usuário não informado'
                    );
                } else {
                    // realizando a instancia da model
                    $this->load->model('M_produtos');

                    // atributo retorno recebe array com informações da consulta
                    $retorno = $this->M_produtos->consultar(
                        $this->getCodigoProduto(),
                        $this->getNomeProduto(),
                        $this->getPrecoProduto(),
                        $this->getDescricaoProduto(),
                        $this->getQuantidadeProduto(),
                        $this->getUsuarioLogin()
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
                'codigo' => 0,
                'msg' => 'O seguinte erro ocorreu: ',
                $e->getMessage()
            );
        }
        echo json_encode($retorno);
    }

    public function alterar()
    {
        /*retornos:
        1 - sucesso
        2 - codigo não informado
        3 - preço não pode ser negativo
        4 - quantidade não pode ser negativa
        5 - nome ou descrição não informado
        6 - dados não encontrados
        7 - usuário não informado*/

        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            $lista = array(
                "codigo_produto" => '0',
                "nome_produto" => '0',
                "preco_produto" => '0',
                "descricao_produto" => '0',
                "quantidade_produto" => '0',
                "usuariologin" => '0',
            );

            if (verificarParam($resultado, $lista) == 1) {
                //setters
                $this->setCodigoProduto($resultado->codigo_produto);
                $this->setNomeProduto($resultado->nome_produto);
                $this->setPrecoProduto($resultado->preco_produto);
                $this->setDescricaoProduto($resultado->descricao_produto);
                $this->setQuantidadeProduto($resultado->quantidade_produto);
                $this->setUsuariologin($resultado->usuariologin);

                //validacoes
                if (trim($this->getCodigoProduto()) == '' || trim($this->getCodigoProduto()) == '0') {
                    $retorno = array(
                        'codigo' => 2,
                        'msg' => 'Código não informado'
                    );
                } elseif ($this->getPrecoProduto() < 0) {
                    $retorno = array(
                        'codigo' => 3,
                        'msg' => 'Preço não pode ser negativo'
                    );
                } elseif ($this->getQuantidadeProduto() < 0) {
                    $retorno = array(
                        'codigo' => 4,
                        'msg' => 'Quantidade não pode ser negativa'
                    );
                } elseif (trim($this->getNomeProduto()) == '' || trim($this->getDescricaoProduto()) == '') {
                    $retorno = array(
                        'codigo' => 5,
                        'msg' => 'Nome ou descrição não informado'
                    );
                } elseif (trim($this->getUsuarioLogin()) == '') {
                    $retorno = array(
                        'codigo' => 7,
                        'msg' => 'Usuário não informado'
                    );
                } else {
                    $this->load->model('M_produtos');

                    $retorno = $this->M_produtos->alterar(
                        $this->getCodigoProduto(),
                        $this->getNomeProduto(),
                        $this->getPrecoProduto(),
                        $this->getDescricaoProduto(),
                        $this->getQuantidadeProduto(),
                        $this->getUsuarioLogin()
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
                'msg' => 'O seguinte erro ocorreu: ',
                $e->getMessage()
            );
        }
        echo json_encode($retorno);
    }

    public function desativar()
    {
        /*retornos:
        1 - produto desativado corretamente
        2 - codigo do produto ão informado
        3 - existem unidades de medidas cadastradas com esse produto
        5 - usuario não informado
        6 - produto não encontrado
        7 - erro no log, mas funfou*/

        try {
            // recebe os parâmetros via JSON
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            // array com os parâmetros esperados
            $lista = array(
                "codigo_produto" => '0',
                "usuarioLogin" => '0'
            );

            if (verificarParam($resultado, $lista) == 1) {
                // setters para os dados recebidos
                $this->setCodigoProduto($resultado->codigo_produto);
                $this->setUsuarioLogin($resultado->usuarioLogin);

                // validações
                if (trim($this->getCodigoProduto()) == '' || trim($this->getCodigoProduto()) == '0') {
                    $retorno = array(
                        "codigo" => 2,
                        "msg" => 'Código do produto não foi informado.'
                    );
                } elseif (trim($this->getUsuarioLogin()) == '') {
                    $retorno = array(
                        "codigo" => 5,
                        "msg" => 'Usuário não informado.'
                    );
                } else {
                    // instancia o model para desativar o produto
                    $this->load->model('ProdutoModel');

                    // verifica se o produto pode ser desativado (verifica dependências)
                    $verificaDependencias = $this->ProdutoModel->verificarDependencias($this->getCodigoProduto());
                    if ($verificaDependencias) {
                        $retorno = array(
                            "codigo" => 3,
                            "msg" => 'Existem unidades de medida cadastradas com este produto. Não é possível desativá-lo.'
                        );
                    } else {
                        // executa o método de desativação
                        $retorno = $this->ProdutoModel->desativarProduto(
                            $this->getCodigoProduto(),
                            $this->getUsuarioLogin()
                        );
                    }
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

        // retorna os dados no formato JSON
        echo json_encode($retorno);
    }
}
