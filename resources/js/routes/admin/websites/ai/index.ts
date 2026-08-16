import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../../wayfinder'
import chatB2e4da from './chat'
import conversations from './conversations'
/**
* @see \App\Http\Controllers\Admin\AiAgentController::chat
 * @see app/Http/Controllers/Admin/AiAgentController.php:46
 * @route '/admin/websites/ai/chat'
 */
export const chat = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chat.url(options),
    method: 'post',
})

chat.definition = {
    methods: ["post"],
    url: '/admin/websites/ai/chat',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\AiAgentController::chat
 * @see app/Http/Controllers/Admin/AiAgentController.php:46
 * @route '/admin/websites/ai/chat'
 */
chat.url = (options?: RouteQueryOptions) => {
    return chat.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AiAgentController::chat
 * @see app/Http/Controllers/Admin/AiAgentController.php:46
 * @route '/admin/websites/ai/chat'
 */
chat.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: chat.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\AiAgentController::chat
 * @see app/Http/Controllers/Admin/AiAgentController.php:46
 * @route '/admin/websites/ai/chat'
 */
    const chatForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: chat.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\AiAgentController::chat
 * @see app/Http/Controllers/Admin/AiAgentController.php:46
 * @route '/admin/websites/ai/chat'
 */
        chatForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: chat.url(options),
            method: 'post',
        })
    
    chat.form = chatForm
const ai = {
    chat: Object.assign(chat, chatB2e4da),
conversations: Object.assign(conversations, conversations),
}

export default ai