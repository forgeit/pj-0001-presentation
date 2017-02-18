(function () {

	'use strict';

	angular
		.module('app')
		.config(routes);

	routes.$inject = ['$routeProvider', '$locationProvider'];

	function routes($routeProvider, $locationProvider) {
		$routeProvider
			.when('/login', {
				templateUrl: 'src/app/login/login.html',
				controller: 'Login',
				controllerAs: 'vm',
				notSecured: true
			});
	}

})();