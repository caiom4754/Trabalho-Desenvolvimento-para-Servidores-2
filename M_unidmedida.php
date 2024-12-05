<?php
defined('BASEPATH') or exit('No direct script access allowed');
class M_unidmedida extends CI_Model
{

    // Função para verificar se a unidade de medida já existe
    public function unidadeMedidaExiste($sigla)
    {
        $sql = "SELECT COUNT(*) as count FROM unid_medida WHERE sigla = ?";
        $query = $this->db->query($sql, array($sigla));
        $result = $query->row();

        return $result->count > 0;
    }

    public function inserir($sigla, $descricao, $usuarioLogin)
    {
        try {
            // Verifica se a unidade de medida já existe
            if ($this->unidadeMedidaExiste($sigla)) {
                return array(
                    'codigo' => 8,
                    'msg' => 'Medida já registrada anteriormente'
                );
            }

            //Query de inserção dos dados
            $sql = "insert into unid_medida (sigla, descricao, usucria)
                values ('$sigla', '$descricao', '$usuarioLogin')";
            $this->db->query($sql);
            //Verificar se a inserção ocorreu com sucesso
            if ($this->db->affected_rows() > 0) {
                //Fazemos a inserção no Log na nuvem
                //Fazemos a instância da model M_log
                $this->load->model('m_log');
                //Fazemos a chamada do método de inserção do Log
                $retorno_log = $this->m_log->inserirLog($usuarioLogin, $sql);
                if ($retorno_log['codigo'] == 1) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Unidade de medida cadastrada corretamente'
                    );
                } else {
                    $dados = array(
                        'codigo' => 7,
                        'msg' => 'Houve algum problema no salvamento do Log, porém,
Unidade de Medida cadastrada corretamente'
                    );
                }
            } else {

                $dados = array(
                    'codigo' => 6,
                    'msg' => 'Houve algum problema na inserção na tabela de unidade de medida'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu ->',
                $e->getMessage(),
                "\n"
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;
    }
    public function consultar($codigo, $sigla, $descricao)
    {
        //------
        //Função que servirá para quatro tipos de consulta:
        // * Para todos as unidades de medida;
        // * Para uma determinada sigla de unidade;
        // * Para um código de unidade de medida;
        // * Para descrição da unidade de medida;
        //---
        try {
            //Query para consultar dados de acordo com parâmetros passados
            $sql = "select * from unid_medida where estatus = '' ";
            if ($codigo != '' && $codigo != 0) {
                $sql = $sql . "and cod_unidade = '$codigo' ";
            }
            if ($sigla != '') {
                $sql = $sql . "and sigla = '$sigla' ";
            }
            if ($descricao != '') {
                $sql = $sql . "and descricao like '%$descricao%' ";
            }
            $retorno = $this->db->query($sql);
            //Verificar se a consulta ocorreu com sucesso
            if ($retorno->num_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg' => 'Consulta efetuada com sucesso',
                    'dados' => $retorno->result()
                );
            } else {
                $dados = array(
                    'codigo' => 6,
                    'msg' => 'Dados não encontrados'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',
                $e->getMessage(),
                "\n"
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;
    }
    public function alterar($codigo, $sigla, $descricao, $usuario)
    {
        try {
            // Verificar se a unidade de medida está desativada
            $queryChecagemEstatus = $this->db->query("SELECT estatus FROM unid_medida WHERE cod_unidade = $codigo");
            $unidadeData = $queryChecagemEstatus->row();

            if ($unidadeData && $unidadeData->estatus === 'D') {
                return array(
                    'codigo' => 7,
                    'msg' => 'Erro: A unidade de medida está desativada e não pode ser atualizada.'
                );
            }

            // Query de atualização dos dados
            if (trim($sigla) != '' && trim($descricao) != '') {
                $sql = "UPDATE unid_medida SET sigla = '$sigla', descricao = '$descricao' WHERE cod_unidade = $codigo";
            } elseif (trim($sigla) != '') {
                $sql = "UPDATE unid_medida SET sigla = '$sigla' WHERE cod_unidade = $codigo";
            } else {
                $sql = "UPDATE unid_medida SET descricao = '$descricao' WHERE cod_unidade = $codigo";
            }

            $this->db->query($sql);

            // Verificar se a atualização ocorreu com sucesso
            if ($this->db->affected_rows() > 0) {
                // Fazemos a inserção no Log na nuvem
                $this->load->model('m_log');
                $retorno_log = $this->m_log->inserirLog($usuario, $sql);

                if ($retorno_log['codigo'] == 1) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Unidade de medida atualizada corretamente'
                    );
                } else {
                    $dados = array(
                        'codigo' => 7,
                        'msg' => 'Houve algum problema no salvamento do Log, porém, unidade de medida atualizada corretamente'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 6,
                    'msg' => 'Houve algum problema na atualização na tabela de unidade de medida'
                );
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        return $dados;
    }
    public function desativar($codigo, $usuario)
    {
        try {
            //Há necessidade de verificar se existe algum produto com
            //essa unidade de medida já cadastrado, se tiver não podemos
            //desativar essa unidade
            $sql = "select * from produtos where unid_medida = '$codigo' and estatus = ''";
            $retorno = $this->db->query($sql);
            //Verificar se a consulta trouxe algum produto
            if ($retorno->num_rows() > 0) {
                //Não posso fazer a desativação
                $dados = array(
                    'codigo' => 3,
                    'msg' => 'Não podemos desativar, existem produtos com essa unidade de medida cadastrado(s).'
                );
            } else {
                //Query de atualização dos dados
                $sql2 = "update unid_medida set estatus = 'D' where cod_unidade = '$codigo'";
                $this->db->query($sql2);
                //Verificar se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    //Fazemos a inserção no Log na nuvem
                    //Fazemos a instância da model M_log
                    $this->load->model('m_log');
                    //Fazemos a chamada do método de inserção do Log
                    $retorno_log = $this->m_log->inserirLog($usuario, $sql2);
                    if ($retorno_log['codigo'] == 1) {
                        $dados = array(
                            'codigo' => 1,
                            'msg' => 'Unidade de medida DESATIVADA corretamente'
                        );
                    } else {
                        $dados = array(
                            'codigo' => 8,
                            'msg' => 'Houve algum problema no salvamento do Log, porém,
                        usuário desativado corretamente'
                        );
                    }
                } else {
                    $dados = array(
                        'codigo' => 7,
                        'msg' => 'Houve algum problema na DESATIVAÇÃO da unidade de medida'
                    );
                }
            }
        } catch (Exception $e) {
            $dados = array(
                'codigo' => 00,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' .
                    $e->getMessage(),
                "\n"
            );
        }
        //Envia o array $dados com as informações tratadas
        //acima pela estrutura de decisão if
        return $dados;
    }
}
