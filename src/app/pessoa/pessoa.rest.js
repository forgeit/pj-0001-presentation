(function () {
	'use strict';

	angular
		.module('app.pessoa')
		.factory('pessoaRest', dataservice);

	dataservice.$inject = ['$http', '$location', '$q', 'configuracaoREST', '$httpParamSerializer'];

	function dataservice($http, $location, $q, configuracaoREST, $httpParamSerializer) {
		var service = {
			atualizar: atualizar,
			buscar: buscar,
			buscarCombo: buscarCombo,
			buscarComboBairro: buscarComboBairro,
			buscarComboFiltro: buscarComboFiltro,
			buscarComboTipoPessoa: buscarComboTipoPessoa,
			buscarComboCidade: buscarComboCidade,
			buscarComboLogradouro: buscarComboLogradouro,
			buscarTodos: buscarTodos,
			remover: remover,
			salvar: salvar
		};

		return service;

		function atualizar(id, data) {
			return $http.put(configuracaoREST.url + configuracaoREST.pessoa + 'atualizar/' + id, data);
		}

		function buscar(data) {	
			return $http.get(configuracaoREST.url + configuracaoREST.pessoa + data);
		}

		function buscarComboTipoPessoa() {
			return $http.get(configuracaoREST.url + 'tipo-pessoa');
		}	

		function buscarCombo() {
			return $http.get(configuracaoREST.url + 'pessoa/combo');
		}	

		function buscarComboFiltro(filtro) {
			return $http.post(configuracaoREST.url + 'pessoa/filtro', filtro);
		}	

		function buscarComboBairro(cidade) {
			return $http.get(configuracaoREST.url + 'endereco/bairro/' + cidade);
		}	

		function buscarComboLogradouro(bairro) {
			return $http.get(configuracaoREST.url + 'endereco/logradouro/' + bairro);
		}	

		function buscarComboCidade() {
			return $http.get(configuracaoREST.url + 'endereco/cidade');
		}	

		function buscarTodos(data) {
			return $http.get(configuracaoREST.url + configuracaoREST.pessoa);
		}

		function remover(data) {
			return $http.delete(configuracaoREST.url + configuracaoREST.pessoa + 'excluir/' + data);
		}

		function salvar(data) {
			return $http.post(configuracaoREST.url + configuracaoREST.pessoa + 'salvar', data);
		}
	}
})();