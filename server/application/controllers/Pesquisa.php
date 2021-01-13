<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesquisa extends CI_Controller {

    public function buscarMinhasPesquisasMobile() {}

	public function buscarUltimaPesquisaMobile() {
        $dados = $this->PesquisaModel->buscarPesquisaUsuario($this->uri->segment(4));

        if (!is_null($dados)) {
            $dados = $dados[0];
            $pesquisa = array();

            $pesquisa['id'] = $dados['id_pesquisa'];
            $pesquisa['tipo'] = array('descricao' => $dados['descricao'], 'icone' => $dados['icone']);
            $pesquisa['titulo'] = $dados['titulo'];
            $pesquisa['resumo'] = $dados['resumo'];
            $pesquisa['validade'] = $dados['data_validade'];
            $pesquisa['criacao'] = $dados['data_criacao'];
            
            if (!file_exists($dados['foto_perfil'])) {
				$dados['foto_perfil'] = __DIR__ . "/../../../src/app/layout/img/perfil.jpg";
			}

			$type = pathinfo($dados['foto_perfil'], PATHINFO_EXTENSION);
			$data = file_get_contents($dados['foto_perfil']);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

            $pesquisa['responsavel'] = array('nome' => $dados['nome'], 'foto' => $base64);

            if (file_exists($dados['imagem'])) {
                $type = pathinfo($dados['imagem'], PATHINFO_EXTENSION);
                $data = file_get_contents($dados['imagem']);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $pesquisa['imagem'] = $base64;
            }
        
            print_r(json_encode(array('data' => array ('pesquisa' => $pesquisa))));
        } else {
            print_r(json_encode(array('data' => array ('pesquisa' => array()))));
        }
	}
	
}