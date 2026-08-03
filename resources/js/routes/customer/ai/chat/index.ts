import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Customer\AiController::stream
 * @see app/Http/Controllers/Customer/AiController.php:139
 * @route '/customer/ai/chat/stream'
 */
export const stream = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stream.url(options),
    method: 'post',
})

stream.definition = {
    methods: ["post"],
    url: '/customer/ai/chat/stream',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Customer\AiController::stream
 * @see app/Http/Controllers/Customer/AiController.php:139
 * @route '/customer/ai/chat/stream'
 */
stream.url = (options?: RouteQueryOptions) => {
    return stream.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Customer\AiController::stream
 * @see app/Http/Controllers/Customer/AiController.php:139
 * @route '/customer/ai/chat/stream'
 */
stream.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stream.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Customer\AiController::stream
 * @see app/Http/Controllers/Customer/AiController.php:139
 * @route '/customer/ai/chat/stream'
 */
    const streamForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: stream.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Customer\AiController::stream
 * @see app/Http/Controllers/Customer/AiController.php:139
 * @route '/customer/ai/chat/stream'
 */
        streamForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: stream.url(options),
            method: 'post',
        })
    
    stream.form = streamForm
const chat = {
    stream: Object.assign(stream, stream),
}

export default chat