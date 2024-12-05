<?php   
defined('BASEPATH') or exit('No direct script access allowed');
class UnidMedida extends CI_Controller
{
    //Atributos privados da classe
    private $codigo;
    private $sigla;
    private $descricao;
    private $usuariologin;
    //Getters dos atributos
    public function getCodigo()
    {
        return $this->codigo;
    }
    public function getSigla()
    {
        return $this->sigla;
    }
    public function getDescricao()
    {
        return $this->descricao;
    }
    public function getUsuarioLogin()
    {
        return $this->usuariologin;
    }
    //Setters dos atributos
    public function setCodigo($codigoFront)
    {
        $this->codigo = $codigoFront;
    }
    public function setSigla($siglaFront)
    {
        $this->sigla = $siglaFront;
    }
    public function setDescricao($descricaoFront)
    {
        $this->descricao = $descricaoFront;
    }
    public function setUsuariologin($usuariologinFront)
    {
        $this->usuariologin = $usuariologinFront;
    }
    public function inserir()
    {
        //Sigla e Descrição
        //recebidas via JSON e colocadas em variáveis
        //Retornos possíveis:
        //1 - Unidade cadastrada corretamente (Banco)
        //2 - Faltou informar a sigla (FrontEnd)
        //3 - Quantidade de caracteres da sigla é supior a 3 (FrontEnd)
        //4 - Descrição não informada (FrontEnd)
        //5 - Usuário não informado (FrontEnd)
        //6 - Houve algum problema no insert da tabela (Banco)
        //7 - Houve problema no salvamento do LOG, mas a unidade foi inclusa (LOG)
        //8 - Unidade já existe no banco de dados (Banco)
        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            //Array com os dados que deverão vir do Front
            $lista = array(
                "sigla" => '0',
                "descricao" => '0',
                "usuariologin" => '0'
            );
            if (verificarParam($resultado, $lista) == 1) {
                //Fazendo os setters
                $this->setSigla($resultado->sigla);
                $this->setDescricao($resultado->descricao);
                $this->setUsuarioLogin($resultado->usuariologin);
                //Faremos uma validação para sabermos se todos os dados
                //foram enviados corretamente
                if (trim($this->getSigla()) == '') {
                    $retorno = array(
                        'codigo' => 2,
                        'msg' => 'Sigla não informada.'
                    );
                } elseif (strlen($this->getSigla()) > 3) {
                    $retorno = array(
                        'codigo' => 3,
                        'msg' => 'Sigla pode conter no máximo 3 caracteres.'
                    );
                } elseif (trim($this->getDescricao()) == '') {
                    $retorno = array(
                        'codigo' => 4,
                        'msg' => 'Descrição não informada.'
                    );
                } elseif (trim($this->getUsuarioLogin()) == '') {
                    $retorno = array(
                        'codigo' => 5,
                        'msg' => 'Usuário não informado'
                    );
                } else {
                    //Realizo a instância da Model
                    $this->load->model('m_unidmedida');
                    $retorno = $this->m_unidmedida->inserir(
                        $this->getSigla(),
                        $this->getDescricao(),
                        $this->getUsuarioLogin()
                    );
                }
            } else {
                $retorno = array(
                    'codigo' => 99,
                    'msg' => 'os campos vindos do front não representam o metodo de login, verifique'
                );
            };
        } catch (Exception $e) {
            $retorno = array(
                'codigo' => 0,
                'msg' => 'o seguinte erro aconteceu ->',
                $e->getMessage()
            );
        }
        //retorno no formato json
        echo json_encode($retorno);
    }
    public function consultar()
    {
        //Código, Sigla e Descrição
        //recebidos via JSON e colocados em variáveis
        //Retornos possíveis:
        //1 - Dados consultados corretamente (Banco)
        //2 - Quantidade de caracteres da sigla é superior a 3 (FrontEnd)
        //6 - Dados não encontrados (Banco)
        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            //Array com os dados que deverão vir do Front
            $lista = array(
                "codigo" => '0',
                "sigla" => '0',
                "descricao" => '0'
            );
            if (verificarParam($resultado, $lista) == 1) {
                //Fazendo os setters
                $this->setCodigo($resultado->codigo);
                $this->setSigla($resultado->sigla);
                $this->setDescricao($resultado->descricao);
                //Verifico somente a qtde de caracteres da sigla, poder ter até 3
                //caracteres ou nenhum para trazer todas as siglas
                if (strlen($this->getSigla()) > 3) {
                    $retorno = array(
                        'codigo' => 2,
                        'msg' => 'Sigla pode conter no máximo 3 caracteres ou nenhum para todas'
                    );
                } else {
                    //Realizo a instância da Model
                    $this->load->model('m_unidmedida');
                    //Atributo $retorno recebe array com informações
                    //da consulta dos dados
                    $retorno = $this->m_unidmedida->consultar(
                        $this->getCodigo(),
                        $this->getSigla(),
                        $this->getDescricao()
                    );
                }
            } else {
                $retorno = array(
                    'codigo' => 99,
                    'msg' => 'Os campos vindos do FrontEnd não representam
                o método de login. Verifique.'
                );
            }
        } catch (Exception $e) {
            $retorno = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu ->
            ' . $e->getMessage()
            );
        }
        echo json_encode($retorno);
    }
    public function alterar()
    {
        //Código, Sigla e Descrição
        //recebidos via JSON e colocadas em variáveis
        //Retornos possíveis:
        //1 - Dado(s) alterado(s) corretamente (Banco)
        //2 - Faltou informar o codigo (FrontEnd)
        //3 - Quantidade de caracteres da sigla é superior a 3 (FrontEnd)
        //4 - Sigla ou Descrição não informadas, aí não tem o que alterar (FrontEnd)
        //5 - Usuário não informado (FrontEnd)
        //6 - Dados não encontrados (Banco)
        //7 - Houve problema no salvamento do LOG, mas a unidade foi inclusa (LOG)
        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);

            $lista = array(
                "codigo" => '0',
                "sigla" => '0',
                "descricao" => '0',
                "usuariologin" => '0'
            );

            if (verificarParam($resultado, $lista) == 1) {
                $this->setCodigo($resultado->codigo);
                $this->setSigla($resultado->sigla);
                $this->setDescricao($resultado->descricao);
                $this->setUsuariologin($resultado->usuariologin);

                if (trim($this->getCodigo()) == '' || trim($this->getCodigo()) == 0) {
                    $retorno = array("codigo" => 2, 'msg' => 'Codigo não informado.');
                } elseif (strlen(trim($this->getSigla())) > 3) {
                    $retorno = array('codigo' => 3, 'msg' => 'Sigla pode conter no máximo 3 caracteres.');
                } elseif (trim($this->getSigla()) == '' || trim($this->getDescricao()) == '') {
                    $retorno = array('codigo' => 4, 'msg' => 'Sigla ou Descrição não foram informadas.');
                } elseif (trim($this->getUsuariologin()) == '') {
                    $retorno = array('codigo' => 5, 'msg' => 'Usuário não informado');
                } else {
                    $this->load->model('m_unidmedida');
                    $retorno = $this->m_unidmedida->alterar($this->getCodigo(), $this->getSigla(), $this->getDescricao(), $this->getUsuarioLogin());
                }
            } else {
                $retorno = array('codigo' => 99, 'msg' => 'Os campos vindos do FrontEnd não representam o método de alteração. Verifique.');
            }
        } catch (Exception $e) {
            $retorno = array('codigo' => 0, 'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage());
        }

        echo json_encode($retorno);
    }

    public function desativar()
    {
        //Código da unidade recebido via JSON e colocado em variável
        //Retornos possíveis:
        //1 - Unidade desativada corretamente (Banco)
        //2 - Código não informado;
        //3 Existem produtos cadastrados com essa unidade de medida
        //5 - Usuário não informado (FrontEnd)
        //6 Dados não encontrados (Banco)
        //7 - Houve problema no salvamento do LOG, mas a unidade foi alterada (LOG)
        try {
            $json = file_get_contents('php://input');
            $resultado = json_decode($json);
            //Array com os dados que deverão vir do Front
            $lista = array(
                "codigo" => '0',
                "usuarioLogin" => '0'
            );
            if (verificarParam($resultado, $lista) == 1) {
                //Fazendo os setters
                $this->setCodigo($resultado->codigo);
                $this->setUsuarioLogin($resultado->usuarioLogin);
                //Validação para tipo de usuário que deverá ser ADMINISTRADOR, COMUM ou VAZIO
                if (trim($this->getCodigo()) == '' || trim($this->getCodigo()) == 0) {
                    $retorno = array(
                        'codigo' => 2,
                        'msg' => 'Código da unidade não informado'
                    );
                } elseif (trim($this->getUsuarioLogin()) == '') {
                    $retorno = array(
                        'codigo' => 5,
                        'msg' => 'Usuário não informado'
                    );
                } else {
                    //Realizo a instância da Model
                    $this->load->model('m_unidmedida');
                    //Atributo $retorno recebe array com informações
                    $retorno = $this->m_unidmedida->desativar(
                        $this->getCodigo(),
                        $this->getUsuarioLogin()
                    );
                }
            } else {
                $retorno = array(
                    'codigo' => 99,
                    'msg' => 'Os campos vindos do FrontEnd não representam o método de Desativar Verifique.'
                );
            }
        } catch (Exception $e) {
            $retorno = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu ->',
                $e->getMessage()
            );
        }
        echo json_encode($retorno);
    }
}
