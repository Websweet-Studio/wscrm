// Stub routes for production build when Wayfinder is not available
// This is a temporary solution to allow builds without Wayfinder

export const home = () => ({ url: '/', method: 'get' });
export const login = () => ({ url: '/login', method: 'get' });
export const register = () => ({ url: '/register', method: 'get' });
export const dashboard = () => ({ url: '/dashboard', method: 'get' });
export const profile = () => ({ url: '/profile', method: 'get' });
export const appearance = () => ({ url: '/settings/appearance', method: 'get' });
export const password = () => ({ url: '/settings/password', method: 'get' });
export const verification = {
    notice: () => ({ url: '/email/verify', method: 'get' }),
};

// Admin routes
export const admin = {
    customers: {
        index: () => ({ url: '/admin/customers', method: 'get' }),
        show: (id: number) => ({ url: `/admin/customers/${id}`, method: 'get' }),
    },
    orders: {
        index: () => ({ url: '/admin/orders', method: 'get' }),
    },
    services: {
        index: () => ({ url: '/admin/services', method: 'get' }),
    },
    invoices: {
        index: () => ({ url: '/admin/invoices', method: 'get' }),
    },
    banks: {
        index: () => ({ url: '/admin/banks', method: 'get' }),
    },
};

// Customer routes
export const customer = {
    dashboard: () => ({ url: '/customer/dashboard', method: 'get' }),
    hosting: {
        index: () => ({ url: '/customer/hosting', method: 'get' }),
    },
    invoices: {
        index: () => ({ url: '/customer/invoices', method: 'get' }),
        payment: (id: number) => ({ url: `/customer/invoices/${id}/payment`, method: 'get' }),
    },
};

// Public routes
export const public_hosting = () => ({ url: '/hosting', method: 'get' });

// Export everything as default for wildcard imports
export default {
    home,
    login,
    register,
    dashboard,
    profile,
    appearance,
    password,
    verification,
    admin,
    customer,
    public_hosting,
};
