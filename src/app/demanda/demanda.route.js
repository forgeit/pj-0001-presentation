(function () {

	'use strict';

	angular
		.module('app')
		.config(routes);

	routes.$inject = ['$routeProvider', '$locationProvider'];

	function routes($routeProvider, $locationProvider) {
		$routeProvider
			.when('/demanda', {
				templateUrl: 'src/app/demanda/demanda-lista.html',
				controller: 'DemandaLista',
				controllerAs: 'vm',
				titulo: 'Demandas',
				cabecalho: {
					h1: 'Demandas',
					breadcrumbs: [
						{
							nome: 'Demandas',
							link: 'demanda',
							ativo: true
						}
					]
				}
			})
			.when('/demanda/:dia/:mes/:ano', {
				templateUrl: 'src/app/demanda/demanda-lista.html',
				controller: 'DemandaLista',
				controllerAs: 'vm',
				titulo: 'Demandas',
				cabecalho: {
					h1: 'Demandas',
					breadcrumbs: [
						{
							nome: 'Demandas',
							link: 'demanda',
							ativo: true
						}
					]
				}
			})
			.when('/nova-demanda', {
				templateUrl: 'src/app/demanda/demanda-form.html',
				controller: 'DemandaForm',
				controllerAs: 'vm',
				titulo: 'Cadastro de Demanda',
				cabecalho: {
					h1: 'Cadastro de Demanda',
					breadcrumbs: [
						{
							nome: 'Demandas',
							link: 'demanda'
						},
						{
							nome: 'Cadastro',
							link: 'nova-demanda',
							ativo: true
						}
					]
				}
			})
			.when('/visualizar-demanda/:id', {
				templateUrl: 'src/app/demanda/demanda-visualizar.html',
				controller: 'DemandaVisualizar',
				controllerAs: 'vm',
				titulo: 'Atualização de Demanda',
				cabecalho: {
					h1: 'Atualização de Demanda',
					breadcrumbs: [
						{
							nome: 'Demandas',
							link: 'demanda'
						},
						{
							nome: 'Atualização',
							link: 'visualizar-demanda',
							ativo: true
						}
					]
				}
			});
	}

})();