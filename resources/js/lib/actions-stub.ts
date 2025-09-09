// Stub actions for production build when database is not available
// This is a temporary solution to allow builds without Wayfinder database dependency

export default {
    store: {
        form: () => ({
            action: '/password/email',
            method: 'post',
        })
    }
};

// Export for different controller imports
export const PasswordResetLinkController = {
    store: {
        form: () => ({
            action: '/password/email',
            method: 'post',
        })
    }
};

export const LoginController = {
    store: {
        form: () => ({
            action: '/login',
            method: 'post',
        })
    }
};

export const RegisterController = {
    store: {
        form: () => ({
            action: '/register',
            method: 'post',
        })
    }
};

export const NewPasswordController = {
    store: {
        form: () => ({
            action: '/reset-password',
            method: 'post',
        })
    }
};

export const EmailVerificationController = {
    store: {
        form: () => ({
            action: '/email/verification-notification',
            method: 'post',
        })
    }
};

export const PasswordController = {
    update: {
        form: () => ({
            action: '/password',
            method: 'put',
        })
    }
};

export const ProfileUpdateController = {
    update: {
        form: () => ({
            action: '/profile',
            method: 'patch',
        })
    }
};

export const UserController = {
    destroy: {
        form: () => ({
            action: '/profile',
            method: 'delete',
        })
    }
};

export const ConfirmPasswordController = {
    store: {
        form: () => ({
            action: '/confirm-password',
            method: 'post',
        })
    }
};