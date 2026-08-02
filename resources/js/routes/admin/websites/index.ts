import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../wayfinder'
import ai335533 from './ai'
import plugins4ebc57 from './plugins'
/**
* @see \App\Http\Controllers\Admin\AiAgentController::ai
 * @see app/Http/Controllers/Admin/AiAgentController.php:23
 * @route '/admin/websites/ai'
 */
export const ai = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ai.url(options),
    method: 'get',
})

ai.definition = {
    methods: ["get","head"],
    url: '/admin/websites/ai',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\AiAgentController::ai
 * @see app/Http/Controllers/Admin/AiAgentController.php:23
 * @route '/admin/websites/ai'
 */
ai.url = (options?: RouteQueryOptions) => {
    return ai.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\AiAgentController::ai
 * @see app/Http/Controllers/Admin/AiAgentController.php:23
 * @route '/admin/websites/ai'
 */
ai.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: ai.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\AiAgentController::ai
 * @see app/Http/Controllers/Admin/AiAgentController.php:23
 * @route '/admin/websites/ai'
 */
ai.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: ai.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\AiAgentController::ai
 * @see app/Http/Controllers/Admin/AiAgentController.php:23
 * @route '/admin/websites/ai'
 */
    const aiForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: ai.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\AiAgentController::ai
 * @see app/Http/Controllers/Admin/AiAgentController.php:23
 * @route '/admin/websites/ai'
 */
        aiForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: ai.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\AiAgentController::ai
 * @see app/Http/Controllers/Admin/AiAgentController.php:23
 * @route '/admin/websites/ai'
 */
        aiForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: ai.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    ai.form = aiForm
/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::plugins
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:24
 * @route '/admin/websites/plugins'
 */
export const plugins = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plugins.url(options),
    method: 'get',
})

plugins.definition = {
    methods: ["get","head"],
    url: '/admin/websites/plugins',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::plugins
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:24
 * @route '/admin/websites/plugins'
 */
plugins.url = (options?: RouteQueryOptions) => {
    return plugins.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::plugins
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:24
 * @route '/admin/websites/plugins'
 */
plugins.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: plugins.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::plugins
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:24
 * @route '/admin/websites/plugins'
 */
plugins.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: plugins.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::plugins
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:24
 * @route '/admin/websites/plugins'
 */
    const pluginsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: plugins.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::plugins
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:24
 * @route '/admin/websites/plugins'
 */
        pluginsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plugins.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\ThirdPartyPluginController::plugins
 * @see app/Http/Controllers/Admin/ThirdPartyPluginController.php:24
 * @route '/admin/websites/plugins'
 */
        pluginsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: plugins.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    plugins.form = pluginsForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:23
 * @route '/admin/websites'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/websites',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:23
 * @route '/admin/websites'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:23
 * @route '/admin/websites'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:23
 * @route '/admin/websites'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:23
 * @route '/admin/websites'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:23
 * @route '/admin/websites'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::index
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:23
 * @route '/admin/websites'
 */
        indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    index.form = indexForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::create
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:46
 * @route '/admin/websites/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/websites/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::create
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:46
 * @route '/admin/websites/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::create
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:46
 * @route '/admin/websites/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::create
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:46
 * @route '/admin/websites/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::create
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:46
 * @route '/admin/websites/create'
 */
    const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: create.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::create
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:46
 * @route '/admin/websites/create'
 */
        createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::create
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:46
 * @route '/admin/websites/create'
 */
        createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: create.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    create.form = createForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:70
 * @route '/admin/websites'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/websites',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:70
 * @route '/admin/websites'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:70
 * @route '/admin/websites'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:70
 * @route '/admin/websites'
 */
    const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: store.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::store
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:70
 * @route '/admin/websites'
 */
        storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: store.url(options),
            method: 'post',
        })
    
    store.form = storeForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:108
 * @route '/admin/websites/{website}'
 */
export const show = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/websites/{website}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:108
 * @route '/admin/websites/{website}'
 */
show.url = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { website: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { website: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    website: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        website: typeof args.website === 'object'
                ? args.website.id
                : args.website,
                }

    return show.definition.url
            .replace('{website}', parsedArgs.website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:108
 * @route '/admin/websites/{website}'
 */
show.get = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:108
 * @route '/admin/websites/{website}'
 */
show.head = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:108
 * @route '/admin/websites/{website}'
 */
    const showForm = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: show.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:108
 * @route '/admin/websites/{website}'
 */
        showForm.get = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::show
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:108
 * @route '/admin/websites/{website}'
 */
        showForm.head = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: show.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    show.form = showForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::edit
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:58
 * @route '/admin/websites/{website}/edit'
 */
export const edit = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/websites/{website}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::edit
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:58
 * @route '/admin/websites/{website}/edit'
 */
edit.url = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { website: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { website: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    website: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        website: typeof args.website === 'object'
                ? args.website.id
                : args.website,
                }

    return edit.definition.url
            .replace('{website}', parsedArgs.website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::edit
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:58
 * @route '/admin/websites/{website}/edit'
 */
edit.get = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::edit
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:58
 * @route '/admin/websites/{website}/edit'
 */
edit.head = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::edit
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:58
 * @route '/admin/websites/{website}/edit'
 */
    const editForm = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: edit.url(args, options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::edit
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:58
 * @route '/admin/websites/{website}/edit'
 */
        editForm.get = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::edit
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:58
 * @route '/admin/websites/{website}/edit'
 */
        editForm.head = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: edit.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'HEAD',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'get',
        })
    
    edit.form = editForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:78
 * @route '/admin/websites/{website}'
 */
export const update = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/websites/{website}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:78
 * @route '/admin/websites/{website}'
 */
update.url = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { website: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { website: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    website: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        website: typeof args.website === 'object'
                ? args.website.id
                : args.website,
                }

    return update.definition.url
            .replace('{website}', parsedArgs.website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:78
 * @route '/admin/websites/{website}'
 */
update.put = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:78
 * @route '/admin/websites/{website}'
 */
update.patch = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:78
 * @route '/admin/websites/{website}'
 */
    const updateForm = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PUT',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:78
 * @route '/admin/websites/{website}'
 */
        updateForm.put = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PUT',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::update
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:78
 * @route '/admin/websites/{website}'
 */
        updateForm.patch = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:86
 * @route '/admin/websites/{website}'
 */
export const destroy = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/websites/{website}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:86
 * @route '/admin/websites/{website}'
 */
destroy.url = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { website: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { website: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    website: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        website: typeof args.website === 'object'
                ? args.website.id
                : args.website,
                }

    return destroy.definition.url
            .replace('{website}', parsedArgs.website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:86
 * @route '/admin/websites/{website}'
 */
destroy.delete = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:86
 * @route '/admin/websites/{website}'
 */
    const destroyForm = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: destroy.url(args, {
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::destroy
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:86
 * @route '/admin/websites/{website}'
 */
        destroyForm.delete = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: destroy.url(args, {
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    destroy.form = destroyForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::sync
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:125
 * @route '/admin/websites/{website}/sync'
 */
export const sync = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sync.url(args, options),
    method: 'post',
})

sync.definition = {
    methods: ["post"],
    url: '/admin/websites/{website}/sync',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::sync
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:125
 * @route '/admin/websites/{website}/sync'
 */
sync.url = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { website: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { website: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    website: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        website: typeof args.website === 'object'
                ? args.website.id
                : args.website,
                }

    return sync.definition.url
            .replace('{website}', parsedArgs.website.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::sync
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:125
 * @route '/admin/websites/{website}/sync'
 */
sync.post = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: sync.url(args, options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::sync
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:125
 * @route '/admin/websites/{website}/sync'
 */
    const syncForm = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: sync.url(args, options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::sync
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:125
 * @route '/admin/websites/{website}/sync'
 */
        syncForm.post = (args: { website: number | { id: number } } | [website: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: sync.url(args, options),
            method: 'post',
        })
    
    sync.form = syncForm
/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:94
 * @route '/admin/websites/bulk'
 */
export const bulkDelete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDelete.url(options),
    method: 'delete',
})

bulkDelete.definition = {
    methods: ["delete"],
    url: '/admin/websites/bulk',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:94
 * @route '/admin/websites/bulk'
 */
bulkDelete.url = (options?: RouteQueryOptions) => {
    return bulkDelete.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:94
 * @route '/admin/websites/bulk'
 */
bulkDelete.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: bulkDelete.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:94
 * @route '/admin/websites/bulk'
 */
    const bulkDeleteForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: bulkDelete.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\WebsiteClientController::bulkDelete
 * @see app/Http/Controllers/Admin/WebsiteClientController.php:94
 * @route '/admin/websites/bulk'
 */
        bulkDeleteForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: bulkDelete.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    bulkDelete.form = bulkDeleteForm
const websites = {
    ai: Object.assign(ai, ai335533),
plugins: Object.assign(plugins, plugins4ebc57),
index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
sync: Object.assign(sync, sync),
bulkDelete: Object.assign(bulkDelete, bulkDelete),
}

export default websites