import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\AiAgentController::stream
 * @see app/Http/Controllers/Admin/AiAgentController.php:121
 * @route '/admin/websites/ai/chat/stream'
 */
export const stream = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stream.url(options),
    method: 'post',
})

stream.definition = {
    methods: ["post"],
    url: '/admin/websites/ai/chat/stream',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AiAgentController::stream
 * @see app/Http/Controllers/Admin/AiAgentController.php:121
 * @route '/admin/websites/ai/chat/stream'
 */
stream.url = (options?: RouteQueryOptions) => {
    return stream.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AiAgentController::stream
 * @see app/Http/Controllers/Admin/AiAgentController.php:121
 * @route '/admin/websites/ai/chat/stream'
 */
stream.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: stream.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AiAgentController::stream
 * @see app/Http/Controllers/Admin/AiAgentController.php:121
 * @route '/admin/websites/ai/chat/stream'
 */
    const streamForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: stream.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AiAgentController::stream
 * @see app/Http/Controllers/Admin/AiAgentController.php:121
 * @route '/admin/websites/ai/chat/stream'
 */
        streamForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: stream.url(options),
            method: 'post',
        })
    
    stream.form = streamForm
/**
* @see \App\Http\Controllers\Admin\AiAgentController::confirm
 * @see app/Http/Controllers/Admin/AiAgentController.php:242
 * @route '/admin/websites/ai/chat/confirm'
 */
export const confirm = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(options),
    method: 'post',
})

confirm.definition = {
    methods: ["post"],
    url: '/admin/websites/ai/chat/confirm',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AiAgentController::confirm
 * @see app/Http/Controllers/Admin/AiAgentController.php:242
 * @route '/admin/websites/ai/chat/confirm'
 */
confirm.url = (options?: RouteQueryOptions) => {
    return confirm.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AiAgentController::confirm
 * @see app/Http/Controllers/Admin/AiAgentController.php:242
 * @route '/admin/websites/ai/chat/confirm'
 */
confirm.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: confirm.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AiAgentController::confirm
 * @see app/Http/Controllers/Admin/AiAgentController.php:242
 * @route '/admin/websites/ai/chat/confirm'
 */
    const confirmForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: confirm.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AiAgentController::confirm
 * @see app/Http/Controllers/Admin/AiAgentController.php:242
 * @route '/admin/websites/ai/chat/confirm'
 */
        confirmForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: confirm.url(options),
            method: 'post',
        })
    
    confirm.form = confirmForm
/**
* @see \App\Http\Controllers\Admin\AiAgentController::cancel
 * @see app/Http/Controllers/Admin/AiAgentController.php:351
 * @route '/admin/websites/ai/chat/cancel'
 */
export const cancel = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(options),
    method: 'post',
})

cancel.definition = {
    methods: ["post"],
    url: '/admin/websites/ai/chat/cancel',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AiAgentController::cancel
 * @see app/Http/Controllers/Admin/AiAgentController.php:351
 * @route '/admin/websites/ai/chat/cancel'
 */
cancel.url = (options?: RouteQueryOptions) => {
    return cancel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AiAgentController::cancel
 * @see app/Http/Controllers/Admin/AiAgentController.php:351
 * @route '/admin/websites/ai/chat/cancel'
 */
cancel.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: cancel.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AiAgentController::cancel
 * @see app/Http/Controllers/Admin/AiAgentController.php:351
 * @route '/admin/websites/ai/chat/cancel'
 */
    const cancelForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: cancel.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AiAgentController::cancel
 * @see app/Http/Controllers/Admin/AiAgentController.php:351
 * @route '/admin/websites/ai/chat/cancel'
 */
        cancelForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: cancel.url(options),
            method: 'post',
        })
    
    cancel.form = cancelForm
const chat = {
    stream: Object.assign(stream, stream),
confirm: Object.assign(confirm, confirm),
cancel: Object.assign(cancel, cancel),
}

export default chat