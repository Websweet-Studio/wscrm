import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\UserCredentialController::sendCredentials
 * @see app/Http/Controllers/Admin/UserCredentialController.php:12
 * @route '/admin/users/{user}/send-credentials'
 */
export const sendCredentials = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendCredentials.url(args, options),
    method: 'post',
})

sendCredentials.definition = {
    methods: ["post"],
    url: '/admin/users/{user}/send-credentials',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\UserCredentialController::sendCredentials
 * @see app/Http/Controllers/Admin/UserCredentialController.php:12
 * @route '/admin/users/{user}/send-credentials'
 */
sendCredentials.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { user: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    user: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        user: typeof args.user === 'object'
                ? args.user.id
                : args.user,
                }

    return sendCredentials.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\UserCredentialController::sendCredentials
 * @see app/Http/Controllers/Admin/UserCredentialController.php:12
 * @route '/admin/users/{user}/send-credentials'
 */
sendCredentials.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sendCredentials.url(args, options),
    method: 'post',
})
const users = {
    sendCredentials: Object.assign(sendCredentials, sendCredentials),
}

export default users