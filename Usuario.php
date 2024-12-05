<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Usuario extends CI_Controller
{
	//Atributos privados da classe
	private $usuario;
	private $senha;
	private $nome;
	private $tipoUsuario;
	private $usuarioLogin;

	//Getters dos atributos
	public function getUsuario()
	{
		return $this->usuario;
	}

	public function getSenha()
	{
		return $this->senha;
	}

	public function getNome()
	{
		return $this->nome;
	}

	public function getTipoUsuario()
	{
		return $this->tipoUsuario;
	}

	public function getUsuarioLogin()
	{
		return $this->usuarioLogin;
	}

	//Setters dos atributos
	public function setUsuario($usuarioFront)
	{
		$this->usuario = $usuarioFront;
	}

	public function setSenha($senhaFront)
	{
		$this->senha = $senhaFront;
	}

	public function setNome($nomeFront)
	{
		$this->nome = $nomeFront;
	}

	public function setTipoUsuario($tipoUsuario)
	{
		$this->tipoUsuario = $tipoUsuario;
	}

	public function setUsuarioLogin($usuarioLoginFront)
	{
		$this->usuarioLogin = $usuarioLoginFront;
	}

	public function inserir()
	{
		//Usuário, senha, nome, tipo (Administrador ou Comum)
		//recebidos via JSON e colocados em variáveis
		//Retornos possíveis:
		//1 - Usuário cadastrado corretamente (Banco)
		//2 - Faltou informar o usuário (FrontEnd)
		//3 - Faltou informar a senha (FrontEnd)
		//4 - Faltou informar o nome (FrontEnd)
		//5 - Faltou informar o tipo de usuário (FrontEnd)
		//6 - Houve algum problema no insert da tabela (Banco)
		//7 - usuario do sistema não informado (front)
		//8 - houve problema no salvamento do log, mas o user foi inserido

		try {
			//Usuário e senha recebidos via JSON 
			//e colocados em atributos
			$json = file_get_contents('php://input');
			$resultado = json_decode($json);

			//Array com os dados que deverão vir do Front
			$lista = array(
				"usuario" => '0',
				"senha" => '0',
				"nome" => '0',
				"tipo_usuario" => '0',
				"usuarioLogin" => '0'
			);

			if (verificarParam($resultado, $lista) == 1) {
				//Fazendo os setters
				$this->setUsuario($resultado->usuario);
				$this->setSenha($resultado->senha);
				$this->setNome($resultado->nome);
				$this->setTipoUsuario(strtoupper($resultado->tipo_usuario));
				$this->setUsuarioLogin($resultado->usuarioLogin);

				//Faremos uma validação para sabermos se todos os dados
				//foram enviados
				if (trim($this->getUsuario()) == '') {
					$retorno = array(
						'codigo' => 2,
						'msg' => 'Usuário não informado'
					);
				} 
				elseif (trim($this->getSenha()) == '') {
					$retorno = array(
						'codigo' => 3,
						'msg' => 'Senha não informada'
					);
				} 
				elseif (trim($this->getNome()) == '') {
					$retorno = array(
						'codigo' => 4,
						'msg' => 'Nome não informado'
					);
				} 
				elseif ((trim($this->getTipoUsuario()) != 'ADMINISTRADOR' &&
						trim($this->getTipoUsuario()) != 'COMUM') ||
					trim($this->getTipoUsuario()) == ''
				) {
					$retorno = array(
						'codigo' => 5,
						'msg' => 'Tipo de usuário inválido'
					);
				} 
				elseif (trim($this->getUsuarioLogin() == '')) {
					$retorno = array(
						'codigo' => 7,
						'msg' => 'usuário do sistema não informado'
					);
				} 
				else {
					//Realizo a instância da Model
					$this->load->model('M_usuario');

					//Atributo $retorno recebe array com informações
					//da validação do acesso
					$retorno = $this-> M_usuario-> inserir(
						$this->getUsuario(),
						$this->getSenha(),
						$this->getNome(),
						$this->getTipoUsuario(),
						$this->getUsuarioLogin()
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
				'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',
				$e->getMessage()
			);
		}

		//Retorno no formato JSON
		echo json_encode($retorno);
	}

	public function consultar()
	{
		//Usuário, nome e tipo (Administrador ou Comum)
		//recebidos via JSON e colocados
		//em variáveis
		//Retornos possíveis:
		//1 - Dados consultados corretamente (Banco)
		//5 - Tipo de usuário inválido (FrontEnd)
		//6 - Dados não encontrados (Banco)
		try {
			$json = file_get_contents('php://input');
			$resultado = json_decode($json);

			//Fazendo os setters
			$this->setUsuario($resultado->usuario);
			$this->setNome($resultado->nome);
			$this->setTipoUsuario(strtoupper($resultado->tipoUsuario));

			//Validação para tipo de usuário que deverá ser ADMINISTRADOR, COMUM ou VAZIO
			if (
				trim($this->getTipoUsuario()) != 'ADMINISTRADOR' &&
				trim($this->getTipoUsuario()) != 'COMUM' &&
				trim($this->getTipoUsuario()) != ''
			) {

				$retorno = array(
					'codigo' => 5,
					'msg' => 'Tipo de usuário inválido'
				);
			} else {
				//Realizo a instância da Model
				$this->load->model('M_usuario');

				//Atributo $retorno recebe array com informações
				//da consulta dos dados
				$retorno = $this->M_usuario->consultar(
					$this->getUsuario(),
					$this->getNome(),
					$this->getTipoUsuario()
				);
			}
		} catch (Exception $e) {
			$retorno = array(
				'codigo' => 0,
				'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',
				$e->getMessage()
			);
		}
		//Retorno no formato JSON
		echo json_encode($retorno);
	}

	public function alterar()
	{
		//Usuário, nome, senha e tipo (Administrador ou Comum)
		//recebidos via JSON e colocados
		//em variáveis
		//Retornos possíveis:
		//1 - Dado(s) alterado(s) corretamente (Banco)
		//2 - Usuario em Branco ou Zerado
		//3 - Nenhum parâmetro de alteração informado.
		//4 - Tipo de usuário inválido (FrontEnd)
		//5 - Dados não encontrados (Banco)

		try {
			$json = file_get_contents('php://input');
			$resultado = json_decode($json);

			//Array com os dados que deverão vir do Front
			$lista = array(
				"usuario" => '0',
				"senha" => '0',
				"nome" => '0',
				"tipo_usuario" => '0'
			);

			if (verificarParam($resultado, $lista) == 1) {
				//Fazendo os setters
				$this->setUsuario($resultado->usuario);
				$this->setSenha($resultado->senha);
				$this->setNome($resultado->nome);
				$this->setTipoUsuario(strtoupper($resultado->tipo_usuario));

				//Validação para tipo de usuário que deverá ser ADMINISTRADOR, COMUM ou VAZIO
				if (
					trim($this->getTipoUsuario()) != 'ADMINISTRADOR' &&
					trim($this->getTipoUsuario()) != 'COMUM' &&
					trim($this->getTipoUsuario()) != ''
				) {

					$retorno = array(
						'codigo' => 4,
						'msg' => 'Tipo de usuário inválido'
					);
					//Validação para usuário
				} elseif (trim($this->getUsuario() == '')) {
					$retorno = array(
						'codigo' => 2,
						'msg' => 'Usuário não informado'
					);
					//Nome, Senha ou Tipo de Usuário, pelo menos 1 deles precisa ser informado.
				} elseif (
					trim($this->getNome()) == '' && trim($this->getSenha() == '')
					&& $this->getTipoUsuario() == ''
				) {
					$retorno = array(
						'codigo' => 3,
						'msg' => 'Pelo menos um parâmetro precisa ser passado para atualização'
					);
				} else {
					//Realizo a instância da Model
					$this->load->model('M_usuario');

					//Atributo $retorno recebe array com informações
					//da alteração dos dados
					$retorno = $this->M_usuario->alterar(
						$this->getUsuario(),
						$this->getNome(),
						$this->getSenha(),
						$this->getTipoUsuario()
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
				'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',
				$e->getMessage()
			);
		}
		//Retorno no formato JSON
		echo json_encode($retorno);
	}

	public function desativar()
	{
		//Usuário recebido via JSON e colocado em variável
		//Retornos possíveis:
		//1 - Usuário desativado corretamente (Banco)
		//2 - Usuário em Branco	
		//3 - Usuário que pediu a desativação não é administrador	
		//4 - Usuário inexistente na base de dados
		//5 - Usuário já desativado na base de dados
		//6 - Dados não encontrados (Banco)
		try {
			$json = file_get_contents('php://input');
			$resultado = json_decode($json);

			//Array com os dados que deverão vir do Front
			$lista = array(
				"usuario" => '0',
				"usuarioLogin" => '0'
			);

			if (verificarParam($resultado, $lista) == 1) {

				$json = file_get_contents('php://input');
				$resultado = json_decode($json);

				//Fazendo os setters
				$this->setUsuario($resultado->usuario);
				$this->setUsuarioLogin($resultado->usuarioLogin);

				//Validação para do usuário que não deverá ser branco
				if (trim($this->getUsuario() == '')) {
					$retorno = array(
						'codigo' => 2,
						'msg' => 'Usuário não informado'
					);
				} else if (trim($this->getUsuarioLogin() == '')) {
					$retorno = array(
						'codigo' => 2,
						'msg' => 'Usuário Logado no sistema não informado'
					);
				} else {
					//Realizo a instância da Model
					$this->load->model('M_usuario');

					//Atributo $retorno recebe array com informações			
					$retorno = $this->M_usuario->desativar(
						$this->getUsuario(),
						$this->getUsuarioLogin()
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
				'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',
				$e->getMessage()
			);
		}

		//Retorno no formato JSON
		echo json_encode($retorno);
	}

	public function logar()
	{
		/////////////////////////////////////////////////////
		//Recebimento via JSON o Usuário e senha
		//Retornos possíveis:
		//1 - Usuário e senha validados corretamente (Banco)
		//2 - Faltou informar o usuário (FrontEnd)
		//3 - Faltou informar a senha (FrontEnd)
		//4 - Usuário ou senha inválidos (Banco)
		//5 - Usuário deletado - Status (Banco)
		//99 - Os campos vindos do FrontEnd não representam o método de login
		////////////////////////////////////////////////////				

		try {
			//Usuário e senha recebidos via JSON 
			//e colocados em atributos		
			$json = file_get_contents('php://input');
			$resultado = json_decode($json);

			//Array com os dados que deverão vir do Front
			$lista = array(
				"usuario" => '0',
				"senha" => '0'
			);

			if (verificarParam($resultado, $lista) == 1) {
				//Fazendo os setters
				$this->setUsuario($resultado->usuario); 
				$this->setSenha($resultado->senha);

				if (trim($this->getUsuario()) == '') {
					$retorno = array(
						'codigo' => 2,
						'msg' => 'Usuário não informado'
					);
				} elseif (trim($this->getSenha()) == '') {
					$retorno = array(
						'codigo' => 3,
						'msg' => 'Senha não informada'
					);
				} else {

					//Realizo a instância da Model
					$this->load->model('M_usuario');

					//Atributo $retorno recebe array com informações
					//da validação do acesso
					$retorno = $this->M_usuario->validaLogin(
						$this->getUsuario(),
						$this->getSenha()
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
				'msg' => 'ATENÇÃO: O seguinte erro aconteceu -> ',
				$e->getMessage()
			);
		}

		//Retorno no formato JSON
		echo json_encode($retorno);
	}
}
