import {defineStore} from 'pinia'

export const useViewStore = defineStore('view', {
    state: () => {
        return {
            viewMode: null,
        }
    },
    getters: {
        isAdminViewMode: (state) => state.viewMode === 'admin',
        isTraderViewMode: (state) => state.viewMode === 'trader',
        isMerchantViewMode: (state) => state.viewMode === 'merchant',
        isTeamLeaderViewMode: (state) => state.viewMode === 'leader',
        adminPrefix: (state) => state.viewMode === 'admin' ? 'admin.' : '',
    },
    actions: {
        setAdminViewMode() {
            this.viewMode = 'admin';
        },
        setTraderViewMode() {
            this.viewMode = 'trader';
        },
        setMerchantViewMode() {
            this.viewMode = 'merchant';
        },
        setTeamLeaderViewMode() {
            this.viewMode = 'leader';
        },
    },
})
