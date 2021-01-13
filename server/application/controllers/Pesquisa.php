<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pesquisa extends CI_Controller {

    public function buscarMinhasPesquisasMobile() {
        $dados = $this->PesquisaModel->buscarMinhasPesquisas($this->uri->segment(4));

        if (!is_null($dados)) {

            foreach ($dados as $key => $value) {
                if (!file_exists($value['foto_perfil'])) {
                    $dados[$key]['foto_perfil'] = __DIR__ . "/../../../src/app/layout/img/perfil.jpg";
                }
    
                $type = pathinfo($dados[$key]['foto_perfil'], PATHINFO_EXTENSION);
                $data = file_get_contents($dados[$key]['foto_perfil']);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    
                $dados[$key]['responsavel'] = array('nome' => $value['responsavel'], 'foto' => $base64);

                unset($dados[$key]['foto_perfil']);
    
                if (file_exists($value['imagem'])) {
                    $type = pathinfo($value['imagem'], PATHINFO_EXTENSION);
                    $data = file_get_contents($value['imagem']);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $dados[$key]['imagem'] = $base64;
                }
            }

            print_r(json_encode(array('data' => array ('pesquisas' => $dados))));
        } else {
            print_r(json_encode(array('data' => array ('pesquisas' => array()))));
        }
    }

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