import { queryParams, type RouteQueryOptions, type RouteDefinition } from './../../../wayfinder'
/**
 * @see routes/admin.php:57
 * @route '/admin/banks'
 */
export const redirect = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: redirect.url(options),
    method: 'get',
})

redirect.definition = {
    methods: ["get","head"],
    url: '/admin/banks',
} satisfies RouteDefinition<["get","head"]>

/**
 * @see routes/admin.php:57
 * @route '/admin/banks'
 */
redirect.url = (options?: RouteQueryOptions) => {
    return redirect.definition.url + queryParams(options)
}

/**
 * @see routes/admin.php:57
 * @route '/admin/banks'
 */
redirect.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: redirect.url(options),
    method: 'get',
})
/**
 * @see routes/admin.php:57
 * @route '/admin/banks'
 */
redirect.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: redirect.url(options),
    method: 'head',
})
const banks = {
    redirect: Object.assign(redirect, redirect),
}

export default banks