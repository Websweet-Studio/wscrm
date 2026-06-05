import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
import orders from './orders'
import services from './services'
import invoices from './invoices'
import settings from './settings'
/**
 * @see routes/customer.php:20
 * @route '/customer/login'
 */
export const login = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ["get","head"],
    url: '/customer/login',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/customer.php:20
 * @route '/customer/login'
 */
login.url = (options?: RouteQueryOptions) => {
    return login.definition.url + queryParams(options)
}

/**
 * @see routes/customer.php:20
 * @route '/customer/login'
 */
login.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: login.url(options),
    method: 'get',
})
/**
 * @see routes/customer.php:20
 * @route '/customer/login'
 */
login.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: login.url(options),
    method: 'head',
})

    /**
 * @see routes/customer.php:20
 * @route '/customer/login'
 */
    const loginForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: login.url(options),
        method: 'get',
    })

            /**
 * @see routes/customer.php:20
 * @route '/customer/login'
 */
        loginForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url(options),
            method: 'get',
        })
            /**
 * @see routes/customer.php:20
 * @route '/customer/login'
 */
        loginForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: login.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    login.form = loginForm
/**
* @see \App\Http\Controllers\Customer\Auth\RegisterController::register
 * @see app/Http/Controllers/Customer/Auth/RegisterController.php:16
 * @route '/customer/register'
 */
export const register = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})

register.definition = {
    methods: ["get","head"],
    url: '/customer/register',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\Auth\RegisterController::register
 * @see app/Http/Controllers/Customer/Auth/RegisterController.php:16
 * @route '/customer/register'
 */
register.url = (options?: RouteQueryOptions) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\Auth\RegisterController::register
 * @see app/Http/Controllers/Customer/Auth/RegisterController.php:16
 * @route '/customer/register'
 */
register.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: register.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\Auth\RegisterController::register
 * @see app/Http/Controllers/Customer/Auth/RegisterController.php:16
 * @route '/customer/register'
 */
register.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: register.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\Auth\RegisterController::register
 * @see app/Http/Controllers/Customer/Auth/RegisterController.php:16
 * @route '/customer/register'
 */
    const registerForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: register.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\Auth\RegisterController::register
 * @see app/Http/Controllers/Customer/Auth/RegisterController.php:16
 * @route '/customer/register'
 */
        registerForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\Auth\RegisterController::register
 * @see app/Http/Controllers/Customer/Auth/RegisterController.php:16
 * @route '/customer/register'
 */
        registerForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: register.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    register.form = registerForm
/**
* @see \App\Http\Controllers\Customer\Auth\LoginController::terms
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:23
 * @route '/customer/terms'
 */
export const terms = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: terms.url(options),
    method: 'get',
})

terms.definition = {
    methods: ["get","head"],
    url: '/customer/terms',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\Auth\LoginController::terms
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:23
 * @route '/customer/terms'
 */
terms.url = (options?: RouteQueryOptions) => {
    return terms.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\Auth\LoginController::terms
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:23
 * @route '/customer/terms'
 */
terms.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: terms.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\Auth\LoginController::terms
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:23
 * @route '/customer/terms'
 */
terms.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: terms.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\Auth\LoginController::terms
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:23
 * @route '/customer/terms'
 */
    const termsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: terms.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\Auth\LoginController::terms
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:23
 * @route '/customer/terms'
 */
        termsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: terms.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\Auth\LoginController::terms
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:23
 * @route '/customer/terms'
 */
        termsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: terms.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    terms.form = termsForm
/**
* @see \App\Http\Controllers\Customer\Auth\LoginController::logout
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:103
 * @route '/customer/logout'
 */
export const logout = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ["post"],
    url: '/customer/logout',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Customer\Auth\LoginController::logout
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:103
 * @route '/customer/logout'
 */
logout.url = (options?: RouteQueryOptions) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\Auth\LoginController::logout
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:103
 * @route '/customer/logout'
 */
logout.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: logout.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Customer\Auth\LoginController::logout
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:103
 * @route '/customer/logout'
 */
    const logoutForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: logout.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Customer\Auth\LoginController::logout
 * @see app/Http/Controllers/Customer/Auth/LoginController.php:103
 * @route '/customer/logout'
 */
        logoutForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: logout.url(options),
            method: 'post',
        })
    
    logout.form = logoutForm
/**
* @see \App\Http\Controllers\Customer\DashboardController::dashboard
 * @see app/Http/Controllers/Customer/DashboardController.php:12
 * @route '/customer/dashboard'
 */
export const dashboard = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ["get","head"],
    url: '/customer/dashboard',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Customer\DashboardController::dashboard
 * @see app/Http/Controllers/Customer/DashboardController.php:12
 * @route '/customer/dashboard'
 */
dashboard.url = (options?: RouteQueryOptions) => {
    return dashboard.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\DashboardController::dashboard
 * @see app/Http/Controllers/Customer/DashboardController.php:12
 * @route '/customer/dashboard'
 */
dashboard.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Customer\DashboardController::dashboard
 * @see app/Http/Controllers/Customer/DashboardController.php:12
 * @route '/customer/dashboard'
 */
dashboard.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: dashboard.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Customer\DashboardController::dashboard
 * @see app/Http/Controllers/Customer/DashboardController.php:12
 * @route '/customer/dashboard'
 */
    const dashboardForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: dashboard.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Customer\DashboardController::dashboard
 * @see app/Http/Controllers/Customer/DashboardController.php:12
 * @route '/customer/dashboard'
 */
        dashboardForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Customer\DashboardController::dashboard
 * @see app/Http/Controllers/Customer/DashboardController.php:12
 * @route '/customer/dashboard'
 */
        dashboardForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: dashboard.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    dashboard.form = dashboardForm
/**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/customer/stop-impersonation'
 */
export const stopImpersonation = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stopImpersonation.url(options),
    method: 'post',
})

stopImpersonation.definition = {
    methods: ["post"],
    url: '/customer/stop-impersonation',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/customer/stop-impersonation'
 */
stopImpersonation.url = (options?: RouteQueryOptions) => {
    return stopImpersonation.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/customer/stop-impersonation'
 */
stopImpersonation.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stopImpersonation.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/customer/stop-impersonation'
 */
    const stopImpersonationForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: stopImpersonation.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/customer/stop-impersonation'
 */
        stopImpersonationForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: stopImpersonation.url(options),
            method: 'post',
        })
    
    stopImpersonation.form = stopImpersonationForm
const customer = {
    login: Object.assign(login, login),
register: Object.assign(register, register),
terms: Object.assign(terms, terms),
logout: Object.assign(logout, logout),
dashboard: Object.assign(dashboard, dashboard),
orders: Object.assign(orders, orders),
services: Object.assign(services, services),
invoices: Object.assign(invoices, invoices),
settings: Object.assign(settings, settings),
stopImpersonation: Object.assign(stopImpersonation, stopImpersonation),
}

export default customer