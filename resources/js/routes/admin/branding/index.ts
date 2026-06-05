import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../../wayfinder'
/**
* @see \App\Http\Controllers\Admin\BrandingController::index
 * @see app/Http/Controllers/Admin/BrandingController.php:18
 * @route '/admin/branding'
 */
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/admin/branding',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Admin\BrandingController::index
 * @see app/Http/Controllers/Admin/BrandingController.php:18
 * @route '/admin/branding'
 */
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BrandingController::index
 * @see app/Http/Controllers/Admin/BrandingController.php:18
 * @route '/admin/branding'
 */
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Admin\BrandingController::index
 * @see app/Http/Controllers/Admin/BrandingController.php:18
 * @route '/admin/branding'
 */
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

    /**
* @see \App\Http\Controllers\Admin\BrandingController::index
 * @see app/Http/Controllers/Admin/BrandingController.php:18
 * @route '/admin/branding'
 */
    const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
        action: index.url(options),
        method: 'get',
    })

            /**
* @see \App\Http\Controllers\Admin\BrandingController::index
 * @see app/Http/Controllers/Admin/BrandingController.php:18
 * @route '/admin/branding'
 */
        indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
            action: index.url(options),
            method: 'get',
        })
            /**
* @see \App\Http\Controllers\Admin\BrandingController::index
 * @see app/Http/Controllers/Admin/BrandingController.php:18
 * @route '/admin/branding'
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
* @see \App\Http\Controllers\Admin\BrandingController::update
 * @see app/Http/Controllers/Admin/BrandingController.php:46
 * @route '/admin/branding'
 */
export const update = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

update.definition = {
    methods: ["patch"],
    url: '/admin/branding',
} satisfies RouteDefinition<["patch"]>

/**
* @see \App\Http\Controllers\Admin\BrandingController::update
 * @see app/Http/Controllers/Admin/BrandingController.php:46
 * @route '/admin/branding'
 */
update.url = (options?: RouteQueryOptions) => {
    return update.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BrandingController::update
 * @see app/Http/Controllers/Admin/BrandingController.php:46
 * @route '/admin/branding'
 */
update.patch = (options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(options),
    method: 'patch',
})

    /**
* @see \App\Http\Controllers\Admin\BrandingController::update
 * @see app/Http/Controllers/Admin/BrandingController.php:46
 * @route '/admin/branding'
 */
    const updateForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: update.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'PATCH',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BrandingController::update
 * @see app/Http/Controllers/Admin/BrandingController.php:46
 * @route '/admin/branding'
 */
        updateForm.patch = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: update.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'PATCH',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    update.form = updateForm
/**
* @see \App\Http\Controllers\Admin\BrandingController::uploadImage
 * @see app/Http/Controllers/Admin/BrandingController.php:87
 * @route '/admin/branding/upload-image'
 */
export const uploadImage = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadImage.url(options),
    method: 'post',
})

uploadImage.definition = {
    methods: ["post"],
    url: '/admin/branding/upload-image',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Admin\BrandingController::uploadImage
 * @see app/Http/Controllers/Admin/BrandingController.php:87
 * @route '/admin/branding/upload-image'
 */
uploadImage.url = (options?: RouteQueryOptions) => {
    return uploadImage.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BrandingController::uploadImage
 * @see app/Http/Controllers/Admin/BrandingController.php:87
 * @route '/admin/branding/upload-image'
 */
uploadImage.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: uploadImage.url(options),
    method: 'post',
})

    /**
* @see \App\Http\Controllers\Admin\BrandingController::uploadImage
 * @see app/Http/Controllers/Admin/BrandingController.php:87
 * @route '/admin/branding/upload-image'
 */
    const uploadImageForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: uploadImage.url(options),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BrandingController::uploadImage
 * @see app/Http/Controllers/Admin/BrandingController.php:87
 * @route '/admin/branding/upload-image'
 */
        uploadImageForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: uploadImage.url(options),
            method: 'post',
        })
    
    uploadImage.form = uploadImageForm
/**
* @see \App\Http\Controllers\Admin\BrandingController::deleteImage
 * @see app/Http/Controllers/Admin/BrandingController.php:136
 * @route '/admin/branding/delete-image'
 */
export const deleteImage = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteImage.url(options),
    method: 'delete',
})

deleteImage.definition = {
    methods: ["delete"],
    url: '/admin/branding/delete-image',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Admin\BrandingController::deleteImage
 * @see app/Http/Controllers/Admin/BrandingController.php:136
 * @route '/admin/branding/delete-image'
 */
deleteImage.url = (options?: RouteQueryOptions) => {
    return deleteImage.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Admin\BrandingController::deleteImage
 * @see app/Http/Controllers/Admin/BrandingController.php:136
 * @route '/admin/branding/delete-image'
 */
deleteImage.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: deleteImage.url(options),
    method: 'delete',
})

    /**
* @see \App\Http\Controllers\Admin\BrandingController::deleteImage
 * @see app/Http/Controllers/Admin/BrandingController.php:136
 * @route '/admin/branding/delete-image'
 */
    const deleteImageForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
        action: deleteImage.url({
                    [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                        _method: 'DELETE',
                        ...(options?.query ?? options?.mergeQuery ?? {}),
                    }
                }),
        method: 'post',
    })

            /**
* @see \App\Http\Controllers\Admin\BrandingController::deleteImage
 * @see app/Http/Controllers/Admin/BrandingController.php:136
 * @route '/admin/branding/delete-image'
 */
        deleteImageForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
            action: deleteImage.url({
                        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
                            _method: 'DELETE',
                            ...(options?.query ?? options?.mergeQuery ?? {}),
                        }
                    }),
            method: 'post',
        })
    
    deleteImage.form = deleteImageForm
const branding = {
    index: Object.assign(index, index),
update: Object.assign(update, update),
uploadImage: Object.assign(uploadImage, uploadImage),
deleteImage: Object.assign(deleteImage, deleteImage),
}

export default branding