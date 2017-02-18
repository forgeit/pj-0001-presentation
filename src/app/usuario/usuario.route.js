(function () {

	'use strict';

	angular
		.module('app')
		.config(routes);

	routes.$inject = ['$routeProvider', '$locationProvider'];

	function routes($routeProvider, $locationProvider) {
		$routeProvider
			.when('/alterar-senha', {
				templateUrl: 'src/app/usuario/alterar-senha.html',
				controller: 'AlterarSenha',
				controllerAs: 'vm',
				titulo: 'Alterar Senha',
				cabecalho: {
					h1: 'Alterar Senha',
					breadcrumbs: [
						{
							nome: 'Alterar Senha',
							link: 'alterar-senha',
							ativo: true
						}
					]
				}
			});
	}

})();