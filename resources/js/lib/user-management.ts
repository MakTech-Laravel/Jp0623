import adminManagement from '@/routes/admin/admin-management';
import userManagement from '@/routes/admin/user-management';

export type UserManagementScope = 'user' | 'admin';

export function managementRoutes(scope: UserManagementScope) {
    return scope === 'admin' ? adminManagement : userManagement;
}

export function managementLabels(scope: UserManagementScope) {
    if (scope === 'admin') {
        return {
            title: 'Admin management',
            singular: 'admin',
            createTitle: 'Create admin',
            editTitle: 'Edit admin',
            addLabel: 'Add admin',
            description: 'Manage administrator accounts.',
            createDescription: 'Add a new administrator account.',
            emptyTitle: 'No admins found',
        };
    }

    return {
        title: 'User management',
        singular: 'user',
        createTitle: 'Create user',
        editTitle: 'Edit user',
        addLabel: 'Add user',
        description: 'Manage standard user accounts.',
        createDescription: 'Add a new user account.',
        emptyTitle: 'No users found',
    };
}
