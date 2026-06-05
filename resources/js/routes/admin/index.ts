import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
import customers from './customers'
import employees from './employees'
import orders from './orders'
import services from './services'
import servicePlans from './service-plans'
import invoices from './invoices'
import domainPrices from './domain-prices'
import hostingPlans from './hosting-plans'
import bulkPricing from './bulk-pricing'
import banks from './banks'
import blog from './blog'
import expenses from './expenses'
import users from './users'
import tasks from './tasks'
import taskCategories from './task-categories'
import branding from './branding'
import database from './database'
import system from './system'
/**
* @see \App\Http\Controllers\Admin\ImpersonateController::impersonate
 * @see app/Http/Controllers/Admin/ImpersonateController.php:11
 * @route '/admin/impersonate/{customer}'
 */
export const impersonate = (args: { customer: number | { id: number } } | [customer: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: impersonate.url(args, options),
    method: 'post',
})

impersonate.definition = {
    methods: ["post"],
    url: '/admin/impersonate/{customer}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ImpersonateController::impersonate
 * @see app/Http/Controllers/Admin/ImpersonateController.php:11
 * @route '/admin/impersonate/{customer}'
 */
impersonate.url = (args: { customer: number | { id: number } } | [customer: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { customer: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { customer: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    customer: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        customer: typeof args.customer === 'object'
                ? args.customer.id
                : args.customer,
                }

    return impersonate.definition.url
            .replace('{customer}', parsedArgs.customer.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ImpersonateController::impersonate
 * @see app/Http/Controllers/Admin/ImpersonateController.php:11
 * @route '/admin/impersonate/{customer}'
 */
impersonate.post = (args: { customer: number | { id: number } } | [customer: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: impersonate.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ImpersonateController::impersonate
 * @see app/Http/Controllers/Admin/ImpersonateController.php:11
 * @route '/admin/impersonate/{customer}'
 */
    const impersonateForm = (args: { customer: number | { id: number } } | [customer: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: impersonate.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ImpersonateController::impersonate
 * @see app/Http/Controllers/Admin/ImpersonateController.php:11
 * @route '/admin/impersonate/{customer}'
 */
        impersonateForm.post = (args: { customer: number | { id: number } } | [customer: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: impersonate.url(args, options),
            method: 'post',
        })
    
    impersonate.form = impersonateForm
/**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/admin/stop-impersonation'
 */
export const stopImpersonation = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stopImpersonation.url(options),
    method: 'post',
})

stopImpersonation.definition = {
    methods: ["post"],
    url: '/admin/stop-impersonation',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/admin/stop-impersonation'
 */
stopImpersonation.url = (options?: RouteQueryOptions) => {
    return stopImpersonation.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/admin/stop-impersonation'
 */
stopImpersonation.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stopImpersonation.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/admin/stop-impersonation'
 */
    const stopImpersonationForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: stopImpersonation.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\ImpersonateController::stopImpersonation
 * @see app/Http/Controllers/Admin/ImpersonateController.php:29
 * @route '/admin/stop-impersonation'
 */
        stopImpersonationForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: stopImpersonation.url(options),
            method: 'post',
        })
    
    stopImpersonation.form = stopImpersonationForm
const admin = {
    customers: Object.assign(customers, customers),
employees: Object.assign(employees, employees),
orders: Object.assign(orders, orders),
services: Object.assign(services, services),
servicePlans: Object.assign(servicePlans, servicePlans),
invoices: Object.assign(invoices, invoices),
domainPrices: Object.assign(domainPrices, domainPrices),
hostingPlans: Object.assign(hostingPlans, hostingPlans),
bulkPricing: Object.assign(bulkPricing, bulkPricing),
banks: Object.assign(banks, banks),
blog: Object.assign(blog, blog),
expenses: Object.assign(expenses, expenses),
impersonate: Object.assign(impersonate, impersonate),
stopImpersonation: Object.assign(stopImpersonation, stopImpersonation),
users: Object.assign(users, users),
tasks: Object.assign(tasks, tasks),
taskCategories: Object.assign(taskCategories, taskCategories),
branding: Object.assign(branding, branding),
database: Object.assign(database, database),
system: Object.assign(system, system),
}

export default admin