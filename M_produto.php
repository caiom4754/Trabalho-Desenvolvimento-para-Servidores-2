<?php
defined('BASEPATH') or exit('No direct script access allowed');
class M_produto extends CI_Model
{

    public function inserir($descricao, $unid_medida, $estoq_minimo, $estoq_maximo, $usucria)
    {
        try {
            // Query de inserção dos dados
            $sql = "INSERT INTO produtos (descricao, unid_medida, estoq_minimo, estoq_maximo, usucria)
                    VALUES ('$descricao', '$unid_medida', '$estoq_minimo', '$estoq_maximo', '$usucria')";

            // Executa a query
            $this->db->query($sql);

            // Verifica se a inserção ocorreu com sucesso
            if ($this->db->affected_rows() > 0) {
                // Inserção na tabela de produtos foi bem-sucedida
                // Carrega o model de log
                $this->load->model('M_Log');

                // Chama o método de inserção no log
                $retornoLog = $this->M_Log->inserirLog($usucria, $sql);

                if ($retornoLog['codigo'] == 1) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Produto cadastrado corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema no salvamento do log, porém o produto foi cadastrado corretamente.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 6,
                    'msg' => 'Houve algum problema na inserção na tabela de produtos.'
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


    public function consultar($codigo, $descricao, $unid_medida, $estoq_minimo, $estoq_maximo, $dtcria, $usucria)
    {
        //--------------------------------------------------
        // Função que servirá para dois tipos de consulta:
        //  * Para todos os produtos;
        //  * Para um determinado produto;
        //--------------------------------------------------
        try {
            // Query para consultar dados de acordo com os parâmetros passados
            $sql = "SELECT * FROM produtos WHERE estatus = '' ";

            if ($codigo != '' && $codigo != 0) {
                $sql = $sql . "AND codigo = '$codigo' ";
            }
            if ($descricao != '') {
                $sql = $sql . "AND descricao LIKE '%$descricao%' ";
            }
            if ($unid_medida != '') {
                $sql = $sql . "AND unid_medida = '$unid_medida' ";
            }
            if ($estoq_minimo != '') {
                $sql = $sql . "AND estoq_minimo >= '$estoq_minimo' ";
            }
            if ($estoq_maximo != '') {
                $sql = $sql . "AND estoq_maximo <= '$estoq_maximo' ";
            }
            if ($dtcria != '') {
                $sql = $sql . "AND dtcria >= '$dtcria' ";
            }
            if ($usucria != '') {
                $sql = $sql . "AND usucria = '$usucria' ";
            }

            $retorno = $this->db->query($sql);

            // Verificar se a consulta ocorreu com sucesso
            if ($retorno->num_rows() > 0) {
                $dados = array(
                    'codigo' => 1,
                    'msg' => 'Consulta efetuada com sucesso!',
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
                'codigo' => 0,
                'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ' . $e->getMessage()
            );
        }

        // Envia o array $dados com as informações tratadas
        // pela estrutura de decisão if
        return $dados;
    }

    public function alterar($codigo, $descricao, $unid_medida, $estoq_minimo, $estoq_maximo, $dtcria, $usucria)
    {
        try {
            // Verificar se o produto está desativado
            $queryChecagemEstatus = $this->db->query("SELECT estatus FROM produtos WHERE cod_produto = $codigo");
            $produtoData = $queryChecagemEstatus->row();

            if ($produtoData && $produtoData->estatus === 'D') {
                return array(
                    'codigo' => 7,
                    'msg' => 'Erro: O produto está desativado e não pode ser atualizado.'
                );
            }

            // Montar a query de atualização com base nos parâmetros fornecidos
            $sql = "UPDATE produtos SET ";
            $camposAlterados = false;

            if (trim($descricao) != '') {
                $sql .= "descricao = '$descricao', ";
                $camposAlterados = true;
            }
            if (trim($unid_medida) != '') {
                $sql .= "unid_medida = '$unid_medida', ";
                $camposAlterados = true;
            }
            if (trim($estoq_minimo) != '') {
                $sql .= "estoq_minimo = '$estoq_minimo', ";
                $camposAlterados = true;
            }
            if (trim($estoq_maximo) != '') {
                $sql .= "estoq_maximo = '$estoq_maximo', ";
                $camposAlterados = true;
            }
            if (trim($dtcria) != '') {
                $sql .= "dtcria = '$dtcria', ";
                $camposAlterados = true;
            }
            if (trim($usucria) != '') {
                $sql .= "usucria = '$usucria', ";
                $camposAlterados = true;
            }

            // Remover a última vírgula se algum campo foi alterado
            if ($camposAlterados) {
                $sql = rtrim($sql, ', ');
                $sql .= " WHERE cod_produto = '$codigo'";

                // Executar a query de atualização
                $this->db->query($sql);

                // Verificar se a atualização ocorreu com sucesso
                if ($this->db->affected_rows() > 0) {
                    // Fazemos a inserção no Log na nuvem
                    $this->load->model('M_Log');
                    $retorno_log = $this->M_Log->inserirLog($usucria, $sql);

                    if ($retorno_log['codigo'] == 1) {
                        $dados = array(
                            'codigo' => 1,
                            'msg' => 'Produto atualizado corretamente.'
                        );
                    } else {
                        $dados = array(
                            'codigo' => 7,
                            'msg' => 'Houve algum problema no salvamento do log, porém o produto foi atualizado corretamente.'
                        );
                    }
                } else {
                    $dados = array(
                        'codigo' => 6,
                        'msg' => 'Houve algum problema na atualização da tabela de produtos.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 6,
                    'msg' => 'Nenhum dado para atualizar.'
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

    public function desativar($codigo, $usucria)
    {
        try {
            // Verificar se o produto já está desativado
            $queryChecagemEstatus = $this->db->query("SELECT estatus FROM produtos WHERE cod_produto = $codigo");
            $produtoData = $queryChecagemEstatus->row();

            if ($produtoData && $produtoData->estatus === 'D') {
                return array(
                    'codigo' => 7,
                    'msg' => 'Erro: O produto já está desativado.'
                );
            }

            // Query de desativação do produto
            $sql = "UPDATE produtos SET estatus = 'D' WHERE cod_produto = '$codigo'";

            $this->db->query($sql);

            // Verificar se a desativação ocorreu com sucesso
            if ($this->db->affected_rows() > 0) {
                // Fazemos a inserção no Log na nuvem
                $this->load->model('M_Log');
                $retorno_log = $this->M_Log->inserirLog($usucria, $sql);

                if ($retorno_log['codigo'] == 1) {
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Produto desativado corretamente.'
                    );
                } else {
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Houve algum problema no salvamento do log, porém o produto foi desativado corretamente.'
                    );
                }
            } else {
                $dados = array(
                    'codigo' => 7,
                    'msg' => 'Houve algum problema na desativação deste produto.'
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

    public function verificaDependenciais($codigo, $usucria)
    {
        try {
            // Função PRIVADA só para verificar se o usuário existe
            // em nosso banco de dados na tabela de usuários
            // Retornos:
            // 1 - Usuário cadastrado na base de dados
            // 8 - Usuário desativado no banco de dados
            // 9 - Usuário não encontrado

            // Query para verificar o usuário na base de dados
            $sql = "SELECT * FROM usuarios WHERE codigo = '$codigo'";

            $retorno = $this->db->query($sql);

            // Verificar se a consulta trouxe algum usuário
            if ($retorno->num_rows() > 0) {
                // Verifico o status do usuário
                if ($retorno->row()->estatus == 'D') {
                    // Usuário desativado
                    $dados = array(
                        'codigo' => 8,
                        'msg' => 'Usuário desativado, não pode realizar operações.'
                    );
                } else {
                    // Usuário ativo
                    $dados = array(
                        'codigo' => 1,
                        'msg' => 'Usuário ativo na base de dados'
                    );
                }
            } else {
                // Usuário não encontrado
                $dados = array(
                    'codigo' => 9,
                    'msg' => 'Usuário não encontrado na base de dados'
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

    public function verificaProduto($descricao)
    {
        try {
            //Query para consultar a unidade de medida		
            $sql = "select * from produtos 
                where descricao = '$descricao'
                and estatus = '' ";

            $retorno = $this->db->query($sql);

            if ($retorno->num_rows() > 0) {
                $dados = array(
                    'codigo' => 10,
                    'msg' => 'Produto já cadastrada na base de dados.'
                );
            } else {
                $dados = array(
                    'codigo' => 2,
                    'msg' => 'Produto não cadastrado.'
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
}
