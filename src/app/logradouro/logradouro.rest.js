(function () {
	'use strict';

	angular
		.module('app.logradouro')
		.factory('logradouroRest', dataservice);

	dataservice.$inject = ['$http', '$location', '$q', 'configuracaoREST', '$httpParamSerializer'];

	function dataservice($http, $location, $q, configuracaoREST, $httpParamSerializer) {
		var service = {
			filtrar: filtrar,
			salvar: salvar,
		};

		return service;

		function filtrar(data) {	
			return $http.post(configuracaoREST.url + 'logradouro/filtrar', data);
		}

		function salvar(data) {	
			return $http.post(configuracaoREST.url + 'logradouro/salvar', data);
		}
	}
})();