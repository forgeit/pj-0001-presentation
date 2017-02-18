(function () {
	'use strict';

	angular
		.module('app.situacao')
		.factory('situacaoRest', dataservice);

	dataservice.$inject = ['$http', '$location', '$q', 'configuracaoREST', '$httpParamSerializer'];

	function dataservice($http, $location, $q, configuracaoREST, $httpParamSerializer) {
		var service = {
			buscarCombo: buscarCombo
		};

		return service;

		function buscarCombo() {
			return $http.get(configuracaoREST.url + 'situacao/combo');
		}	
	}
})();