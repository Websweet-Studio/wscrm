import { queryParams, type RouteQueryOptions, type RouteDefinition, applyUrlDefaults } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\BlogController::index
 * @see app/Http/Controllers/Admin/BlogController.php:19
 * @route '/admin/blog'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/blog',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BlogController::index
 * @see app/Http/Controllers/Admin/BlogController.php:19
 * @route '/admin/blog'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BlogController::index
 * @see app/Http/Controllers/Admin/BlogController.php:19
 * @route '/admin/blog'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BlogController::index
 * @see app/Http/Controllers/Admin/BlogController.php:19
 * @route '/admin/blog'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\BlogController::create
 * @see app/Http/Controllers/Admin/BlogController.php:60
 * @route '/admin/blog/create'
 */
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/admin/blog/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BlogController::create
 * @see app/Http/Controllers/Admin/BlogController.php:60
 * @route '/admin/blog/create'
 */
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BlogController::create
 * @see app/Http/Controllers/Admin/BlogController.php:60
 * @route '/admin/blog/create'
 */
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BlogController::create
 * @see app/Http/Controllers/Admin/BlogController.php:60
 * @route '/admin/blog/create'
 */
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\BlogController::store
 * @see app/Http/Controllers/Admin/BlogController.php:72
 * @route '/admin/blog'
 */
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/admin/blog',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\BlogController::store
 * @see app/Http/Controllers/Admin/BlogController.php:72
 * @route '/admin/blog'
 */
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BlogController::store
 * @see app/Http/Controllers/Admin/BlogController.php:72
 * @route '/admin/blog'
 */
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Admin\BlogController::show
 * @see app/Http/Controllers/Admin/BlogController.php:111
 * @route '/admin/blog/{blog}'
 */
export const show = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/admin/blog/{blog}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BlogController::show
 * @see app/Http/Controllers/Admin/BlogController.php:111
 * @route '/admin/blog/{blog}'
 */
show.url = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { blog: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { blog: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    blog: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        blog: typeof args.blog === 'object'
                ? args.blog.id
                : args.blog,
                }

    return show.definition.url
            .replace('{blog}', parsedArgs.blog.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BlogController::show
 * @see app/Http/Controllers/Admin/BlogController.php:111
 * @route '/admin/blog/{blog}'
 */
show.get = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BlogController::show
 * @see app/Http/Controllers/Admin/BlogController.php:111
 * @route '/admin/blog/{blog}'
 */
show.head = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\BlogController::edit
 * @see app/Http/Controllers/Admin/BlogController.php:123
 * @route '/admin/blog/{blog}/edit'
 */
export const edit = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/admin/blog/{blog}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BlogController::edit
 * @see app/Http/Controllers/Admin/BlogController.php:123
 * @route '/admin/blog/{blog}/edit'
 */
edit.url = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { blog: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { blog: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    blog: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        blog: typeof args.blog === 'object'
                ? args.blog.id
                : args.blog,
                }

    return edit.definition.url
            .replace('{blog}', parsedArgs.blog.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BlogController::edit
 * @see app/Http/Controllers/Admin/BlogController.php:123
 * @route '/admin/blog/{blog}/edit'
 */
edit.get = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BlogController::edit
 * @see app/Http/Controllers/Admin/BlogController.php:123
 * @route '/admin/blog/{blog}/edit'
 */
edit.head = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Admin\BlogController::update
 * @see app/Http/Controllers/Admin/BlogController.php:136
 * @route '/admin/blog/{blog}'
 */
export const update = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/admin/blog/{blog}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\Admin\BlogController::update
 * @see app/Http/Controllers/Admin/BlogController.php:136
 * @route '/admin/blog/{blog}'
 */
update.url = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { blog: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { blog: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    blog: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        blog: typeof args.blog === 'object'
                ? args.blog.id
                : args.blog,
                }

    return update.definition.url
            .replace('{blog}', parsedArgs.blog.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BlogController::update
 * @see app/Http/Controllers/Admin/BlogController.php:136
 * @route '/admin/blog/{blog}'
 */
update.put = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\Admin\BlogController::update
 * @see app/Http/Controllers/Admin/BlogController.php:136
 * @route '/admin/blog/{blog}'
 */
update.patch = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\BlogController::destroy
 * @see app/Http/Controllers/Admin/BlogController.php:176
 * @route '/admin/blog/{blog}'
 */
export const destroy = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/admin/blog/{blog}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\BlogController::destroy
 * @see app/Http/Controllers/Admin/BlogController.php:176
 * @route '/admin/blog/{blog}'
 */
destroy.url = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { blog: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { blog: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    blog: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        blog: typeof args.blog === 'object'
                ? args.blog.id
                : args.blog,
                }

    return destroy.definition.url
            .replace('{blog}', parsedArgs.blog.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BlogController::destroy
 * @see app/Http/Controllers/Admin/BlogController.php:176
 * @route '/admin/blog/{blog}'
 */
destroy.delete = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Admin\BlogController::toggleFeatured
 * @see app/Http/Controllers/Admin/BlogController.php:191
 * @route '/admin/blog/{blog}/toggle-featured'
 */
export const toggleFeatured = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleFeatured.url(args, options),
    method: 'patch',
})

toggleFeatured.definition = {
    methods: ["patch"],
    url: '/admin/blog/{blog}/toggle-featured',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\BlogController::toggleFeatured
 * @see app/Http/Controllers/Admin/BlogController.php:191
 * @route '/admin/blog/{blog}/toggle-featured'
 */
toggleFeatured.url = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { blog: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { blog: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    blog: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        blog: typeof args.blog === 'object'
                ? args.blog.id
                : args.blog,
                }

    return toggleFeatured.definition.url
            .replace('{blog}', parsedArgs.blog.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BlogController::toggleFeatured
 * @see app/Http/Controllers/Admin/BlogController.php:191
 * @route '/admin/blog/{blog}/toggle-featured'
 */
toggleFeatured.patch = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: toggleFeatured.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\Admin\BlogController::togglePinned
 * @see app/Http/Controllers/Admin/BlogController.php:201
 * @route '/admin/blog/{blog}/toggle-pinned'
 */
export const togglePinned = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: togglePinned.url(args, options),
    method: 'patch',
})

togglePinned.definition = {
    methods: ["patch"],
    url: '/admin/blog/{blog}/toggle-pinned',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\BlogController::togglePinned
 * @see app/Http/Controllers/Admin/BlogController.php:201
 * @route '/admin/blog/{blog}/toggle-pinned'
 */
togglePinned.url = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { blog: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { blog: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    blog: args[0],
                }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
                        blog: typeof args.blog === 'object'
                ? args.blog.id
                : args.blog,
                }

    return togglePinned.definition.url
            .replace('{blog}', parsedArgs.blog.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BlogController::togglePinned
 * @see app/Http/Controllers/Admin/BlogController.php:201
 * @route '/admin/blog/{blog}/toggle-pinned'
 */
togglePinned.patch = (args: { blog: number | { id: number } } | [blog: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: togglePinned.url(args, options),
    method: 'patch',
})
const blog = {
    index: Object.assign(index, index),
create: Object.assign(create, create),
store: Object.assign(store, store),
show: Object.assign(show, show),
edit: Object.assign(edit, edit),
update: Object.assign(update, update),
destroy: Object.assign(destroy, destroy),
toggleFeatured: Object.assign(toggleFeatured, toggleFeatured),
togglePinned: Object.assign(togglePinned, togglePinned),
}

export default blog