(function () {
	'use strict';

	angular
		.module('app.usuario')
		.factory('usuarioRest', dataservice);

	dataservice.$inject = ['$http', '$location', '$q', 'configuracaoREST', '$httpParamSerializer'];

	function dataservice($http, $location, $q, configuracaoREST, $httpParamSerializer) {
		var service = {
			atualizar: atualizar
		};

		return service;

		function atualizar(id, data) {
			return $http.post(configuracaoREST.url + 'usuario/alterar-senha/' + id, data);
		}
	}
})();