<?php
defined('BASEPATH') or exit('No direct script access allowed');

class M_usuario extends CI_Model {
	public function inserir($usuario, $senha, $nome, $tipo_usuario, $usuarioLogin){
		try{
			//Query de inserção dos dados
			$sql = "insert into usuarios (usuario, senha, nome, tipo)
							values ('$usuario', md5('$senha'), '$nome', '$tipo_usuario')";

			$this ->db -> query($sql);

			//Verificar se a inserção ocorreu com sucesso
			if($this->db->affected_rows() > 0){
			//usuario na base inseriu normal
			//instancia do banco
			$this -> load -> model('M_Log');
			
			//fazemos a chamada do metodo de inserção
			$retornoLog = $this-> M_Log -> inserirLog($usuarioLogin, $sql);

				if($retornoLog['codigo'] == 1){
					$dados = array('codigo' => 1,
									'msg' => 'usuario cadastrado corretamente');
				}
				else{
					$dados = array('codigo' => 8,
									'msg' => 'houve algum, problema no salvamento do log, porem usuario cadastrado corretamente');
				}

			}else{
				$dados = array('codigo' => 6,
							'msg' => 'Houve algum problema na inserção na tabela de usuários');
			}
		} catch (Exception $e) {
			$dados = array('codigo' => 00,
			               'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',  
						            $e->getMessage(), "\n");
		}
		//Envia o array $dados com as informações tratadas
		//acima pela estrutura de decisão if
		return $dados;
	}

	public function consultar($usuario, $nome, $tipo_usuario){
		//--------------------------------------------------
		//Função que servirá para quatro tipos de consulta:
		//  * Para todos os usuários;
		//  * Para um determinado usuário;
		//  * Para um tipo de usuário;
		//  * Para nomes de usuários;
		//--------------------------------------------------

		try{
			//Query para consultar dados de acordo com parâmetros passados		
			$sql = "select * from usuarios where estatus = '' ";

			if(trim($usuario) != '') {
				$sql = $sql . "and usuario = '$usuario' ";
			}

			if(trim($tipo_usuario) != ''){
				$sql = $sql . "and tipo = '$tipo_usuario' ";
			}
			
			if(trim($nome) != ''){
				$sql = $sql . "and nome like '%$nome%' ";
			}

			$retorno = $this->db->query($sql);
			
			//Verificar se a consulta ocorreu com sucesso
			if($retorno->num_rows() > 0){
				$dados = array('codigo' => 1,
							'msg' => 'Consulta efetuada com sucesso',
							'dados' => $retorno->result());
				
			}else{
				$dados = array('codigo' => 6,
							'msg' => 'Dados não encontrados');
			}
		}catch (Exception $e) {
			$dados = array('codigo' => 00,
							'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',  
									$e->getMessage(), "\n");
		}
		//Envia o array $dados com as informações tratadas
		//acima pela estrutura de decisão if
		return $dados;
	}

	public function alterar($usuario, $nome, $senha, $tipo_usuario){

		//Inicio a query para atualização
		$query = "update usuarios set ";

		try{
			//Vamos comparar os items
			if($nome !== ''){
				$query .= "nome = '$nome', ";
			}

			if($senha !== ''){
				$query .= "senha = md5('$senha'), ";
			}

			if($tipo_usuario !== ''){
				$query .= "tipo = '$tipo_usuario', ";
			}

			//Termino a concatenção da query
			$queryFinal = rtrim($query, ", ") . " where usuario = '$usuario'";

			//Executo a Query de atualização dos dados
			$this->db->query($queryFinal);

			//Verificar se a atualização ocorreu com sucesso
			if($this->db->affected_rows() > 0){
				$dados = array('codigo' => 1,
							'msg' => 'Usuário atualizado corretamente');
				
			}else{
				$dados = array('codigo' => 6,
							'msg' => 'Houve algum problema na atualização na tabela de usuários');
			}
		}catch (Exception $e) {
			$dados = array('codigo' => 00,
							'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',  
									$e->getMessage(), "\n");
		}
		//Envia o array $dados com as informações tratadas
		//acima pela estrutura de decisão if
		return $dados;
	}

	public function desativar($usuario, $usuarioLogin){
		try{	
			//Verificar o tipo de usuario do solicitante a desativação
			$retornoAdmin = $this->verificaAdmin($usuarioLogin);

			if($retornoAdmin == 1){
				//Verificar o status do usuário antes de fazer o update
				$retornoUsuario = $this->validaUsuario($usuario);

				if($retornoUsuario['codigo'] == 1){
					//Query de atualização dos dados
					$this->db->query("update usuarios set estatus = 'D'
									where usuario = '$usuario'");

					//Verificar se a atualização ocorreu com sucesso
					if($this->db->affected_rows() > 0){
						$dados = array('codigo' => 1,
									'msg' => 'Usuário DESATIVADO corretamente');
						
					}else{
						$dados = array('codigo' => 6,
									'msg' => 'Houve algum problema na DESATIVAÇÃO do usuário');
					}
				}else{
					$dados = array('codigo' => $retornoUsuario['codigo'],
									'msg' => $retornoUsuario['msg']);
				}
			}else{
				$dados = array('codigo' => 3,
								'msg' => 'Usuário NÃO É ADMINISTRADOR, não pode excluir.');			
			}
		}catch (Exception $e) {
			$dados = array('codigo' => 00,
							'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',  
									$e->getMessage(), "\n");
		}
		//Envia o array $dados com as informações tratadas
		//acima pela estrutura de decisão if
		return $dados;
	}

	private function verificaAdmin($usuario){
		try{
			//Verificação se o usuário passado é administrador
			$retorno = $this->db->query("select * from usuarios
										where usuario = '$usuario'
										  and tipo = 'ADMINISTRADOR'
										  and estatus = ''");

			//Verifica se a quantidade de linhas trazidas na consulta é superior a 0
			if($retorno->num_rows() == 0){
				//Usuário não é administrador
				$dado = 0;
			}else{
				//Usuário é administrador
				$dado = 1;
			}
			
		} catch (Exception $e) {
			$dado = 0;
		}		

		return $dado;

	}

	private function validaUsuario($usuario){
		try{
			//Atributo retorno recebe o resultado do SELECT
			//realizado na tabela de usuários lembrando da função MD5()
			//por causa da criptografia, e sem status pois teremos que validar
			//para verificar se está deletado virtualmente ou não.
			$retorno = $this->db->query("select * from usuarios
										where usuario = '$usuario'");

			//Verifica se a quantidade de linhas trazidas na consulta é superior a 0, 
			//Vinculamos o resultado da query para tratarmos o resultado do status
			$linha = $retorno->row();

			//Criado um array com dois elementos para retorno do resultado
			//1 - Codigo da mensagem
			//2 - Descrição da mensagem

			if($retorno->num_rows() == 0){
				$dados = array('codigo' => 4,
							   'msg' => 'Usuário não existe na base de dados.');
			}else{
				if(trim($linha->estatus) == "D"){
					$dados = array('codigo' => 5,
								   'msg' => 'Usuário JÁ DESATIVADO NA BASE DE DADOS!');
				}else{
					$dados = array('codigo' => 1,
								   'msg' => 'Usuário correto');
				}
			}
			
		} catch (Exception $e) {
			$dados = array('codigo' => 00,
			               'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',  
						            $e->getMessage(), "\n");
		}		

		return $dados;
	}

	public function validaLogin($usuario, $senha){
		try{
			//Atributo retorno recebe o resultado do SELECT
			//realizado na tabela de usuários lembrando da função MD5()
			//por causa da criptografia, e sem status pois teremos que validar
			//para verificar se está deletado virtualmente ou não.
			$retorno = $this->db->query("select * from usuarios
										where usuario = '$usuario'
										and senha   = md5('$senha')");

			//Verifica se a quantidade de linhas trazidas na consulta é superior a 0, 
			//Vinculamos o resultado da query para tratarmos o resultado do status
			$linha = $retorno->row();

			//Criado um array com dois elementos para retorno do resultado
			//1 - Codigo da mensagem
			//2 - Descrição da mensagem

			if($retorno->num_rows() == 0){
				$dados = array('codigo' => 4,
							   'msg' => 'Usuário ou senha inválidos.');
			}else{
				if(trim($linha->estatus) == "D"){
					$dados = array('codigo' => 5,
								   'msg' => 'Usuário DESATIVADO NA BASE DE DADOS!');
				}else{
					$dados = array('codigo' => 1,
								   'msg' => 'Usuário correto');
				}
			}

		} catch (Exception $e) {
			$dados = array('codigo' => 00,
			               'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',  
						            $e->getMessage(), "\n");
		}

		return $dados;
	}
	
}

