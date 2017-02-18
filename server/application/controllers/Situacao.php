<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Situacao extends MY_Controller {
	public function buscarCombo() {
		$lista = $this->SituacaoModel->buscarCombo();
		print_r(json_encode(array('data' => array ('ArrayList' => $lista ? $lista : array()))));
	}
}