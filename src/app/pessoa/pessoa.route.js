(function () {

	'use strict';

	angular
		.module('app')
		.config(routes);

	routes.$inject = ['$routeProvider', '$locationProvider'];

	function routes($routeProvider, $locationProvider) {
		$routeProvider
			.when('/pessoa', {
				templateUrl: 'src/app/pessoa/pessoa-lista.html',
				controller: 'PessoaLista',
				controllerAs: 'vm',
				titulo: 'Pessoas',
				cabecalho: {
					h1: 'Pessoas',
					breadcrumbs: [
						{
							nome: 'Pessoas',
							link: 'pessoa',
							ativo: true
						}
					]
				}
			})
			.when('/nova-pessoa', {
				templateUrl: 'src/app/pessoa/pessoa-form.html',
				controller: 'PessoaForm',
				controllerAs: 'vm',
				titulo: 'Cadastro de Pessoa',
				cabecalho: {
					h1: 'Cadastro de Pessoa',
					breadcrumbs: [
						{
							nome: 'Pessoas',
							link: 'pessoa'
						},
						{
							nome: 'Cadastro',
							link: 'nova-pessoa',
							ativo: true
						}
					]
				}
			})
			.when('/nova-pessoa/:id', {
				templateUrl: 'src/app/pessoa/pessoa-form.html',
				controller: 'PessoaForm',
				controllerAs: 'vm',
				titulo: 'Cadastro de Pessoa',
				cabecalho: {
					h1: 'Cadastro de Pessoa',
					breadcrumbs: [
						{
							nome: 'Pessoas',
							link: 'pessoa'
						},
						{
							nome: 'Cadastro',
							link: 'nova-pessoa',
							ativo: true
						}
					]
				}
			});
	}

})();