(function () {

	'use strict';

	angular
		.module('app')
		.config(routes);

	routes.$inject = ['$routeProvider', '$locationProvider'];

	function routes($routeProvider, $locationProvider) {
		$routeProvider
			.when('/tipo-demanda', {
				templateUrl: 'src/app/tipo-demanda/tipo-demanda-lista.html',
				controller: 'TipoDemandaLista',
				controllerAs: 'vm',
				titulo: 'Tipos de Demanda',
				cabecalho: {
					h1: 'Tipos de Demanda',
					breadcrumbs: [
						{
							nome: 'Tipos de Demanda',
							link: 'tipo-demanda',
							ativo: true
						}
					]
				}
			})
			.when('/nova-tipo-demanda', {
				templateUrl: 'src/app/tipo-demanda/tipo-demanda-form.html',
				controller: 'TipoDemandaForm',
				controllerAs: 'vm',
				titulo: 'Cadastro de Tipo de Demanda',
				cabecalho: {
					h1: 'Cadastro de Tipo de Demanda',
					breadcrumbs: [
						{
							nome: 'Tipos de Demanda',
							link: 'tipo-demanda'
						},
						{
							nome: 'Cadastro',
							link: 'nova-tipo-demanda',
							ativo: true
						}
					]
				}
			})
			.when('/nova-tipo-demanda/:id', {
				templateUrl: 'src/app/tipo-demanda/tipo-demanda-form.html',
				controller: 'TipoDemandaForm',
				controllerAs: 'vm',
				titulo: 'Cadastro de Tipo de Demanda',
				cabecalho: {
					h1: 'Cadastro de Tipo de Demanda',
					breadcrumbs: [
						{
							nome: 'Tipos de Demanda',
							link: 'tipo-demanda'
						},
						{
							nome: 'Cadastro',
							link: 'nova-tipo-demanda',
							ativo: true
						}
					]
				}
			});
	}

})();