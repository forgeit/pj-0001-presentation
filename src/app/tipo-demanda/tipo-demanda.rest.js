(function () {
	'use strict';

	angular
		.module('app.tipo-demanda')
		.factory('tipoDemandaRest', dataservice);

	dataservice.$inject = ['$http', '$location', '$q', 'configuracaoREST', '$httpParamSerializer'];

	function dataservice($http, $location, $q, configuracaoREST, $httpParamSerializer) {
		var service = {
			atualizar: atualizar,
			buscar: buscar,
			buscarTodos: buscarTodos,
			remover: remover,
			salvar: salvar
		};

		return service;

		function atualizar(id, data) {
			return $http.put(configuracaoREST.url + configuracaoREST.tipoDemanda + 'salvar/' + id, data);
		}

		function buscar(data) {	
			return $http.get(configuracaoREST.url + configuracaoREST.tipoDemanda + data);
		}


		function buscarTodos(data) {
			return $http.get(configuracaoREST.url + configuracaoREST.tipoDemanda);
		}

		function remover(data) {
			return $http.delete(configuracaoREST.url + configuracaoREST.tipoDemanda + 'excluir/' + data);
		}

		function salvar(data) {
			return $http.post(configuracaoREST.url + configuracaoREST.tipoDemanda + 'salvar', data);
		}
	}
})();