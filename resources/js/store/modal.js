import {defineStore} from 'pinia'

export const useModalStore = defineStore('modal', {
    state: () => {
        return {
            modals: {
                confirm: {
                    showed: false,
                    params: {},
                },
                dispute: {
                    showed: false,
                    params: {},
                },
                disputeCancel: {
                    showed: false,
                    params: {},
                },
                deposit: {
                    showed: false,
                    params: {},
                },
                traderDeposit: {
                    showed: false,
                    params: {},
                },
                withdrawal: {
                    showed: false,
                    params: {},
                },
                order: {
                    showed: false,
                    params: {},
                },
                notification: {
                    showed: false,
                    params: {},
                },
                payout: {
                    showed: false,
                    params: {},
                },
                editOrderAmount: {
                    showed: false,
                    params: {},
                },
                userNotes: {
                    showed: false,
                    params: {},
                },
                userCreate: {
                    showed: false,
                    params: {},
                },
                userEdit: {
                    showed: false,
                    params: {},
                },
                paymentDetailCreate: {
                    showed: false,
                    params: {},
                },
                paymentDetailEdit: {
                    showed: false,
                    params: {},
                },
                merchantCreate: {
                    showed: false,
                    params: {},
                },
                paymentCreate: {
                    showed: false,
                    params: {},
                },
                supportCreate: {
                    showed: false,
                    params: {},
                },
                supportEdit: {
                    showed: false,
                    params: {},
                },
                promoCodeCreate: {
                    showed: false,
                    params: {},
                },
                promoCodeEdit: {
                    showed: false,
                    params: {},
                },
            },
        }
    },
    getters: {
        confirmModal: (state) => state.modals.confirm,
        disputeModal: (state) => state.modals.dispute,
        disputeCancelModal: (state) => state.modals.disputeCancel,
        depositModal: (state) => state.modals.deposit,
        withdrawalModal: (state) => state.modals.withdrawal,
        orderModal: (state) => state.modals.order,
        notificationModal: (state) => state.modals.notification,
        payoutModal: (state) => state.modals.payout,
        editOrderAmountModal: (state) => state.modals.editOrderAmount,
        userNotesModal: (state) => state.modals.userNotes,
        traderDepositModal: (state) => state.modals.traderDeposit,
        userCreateModal: (state) => state.modals.userCreate,
        userEditModal: (state) => state.modals.userEdit,
        paymentDetailCreateModal: (state) => state.modals.paymentDetailCreate,
        paymentDetailEditModal: (state) => state.modals.paymentDetailEdit,
        merchantCreateModal: (state) => state.modals.merchantCreate,
        paymentCreateModal: (state) => state.modals.paymentCreate,
        supportCreateModal: (state) => state.modals.supportCreate,
        supportEditModal: (state) => state.modals.supportEdit,
        promoCodeCreateModal: (state) => state.modals.promoCodeCreate,
        promoCodeEditModal: (state) => state.modals.promoCodeEdit,
    },
    actions: {
        openModal(name, params = {}) {
            this.modals[name].showed = true;
            this.modals[name].params = params;
        },
        closeModal(name) {
            this.modals[name].showed = false;
            this.modals[name].params = {};
        },
        openConfirmModal({
             title,
             body = 'Действие невозможно отменить.',
             confirm_button_name = 'Подтвердить',
             cancel_button_name = 'Отмена',
             confirm = null,
             close = null
        } = {}) {
            this.openModal('confirm', {
                title,
                body,
                confirm_button_name,
                cancel_button_name,
                confirm,
                close
            });
        },
        openDisputeModal(props) {
            this.openModal('dispute', props);
        },
        openDisputeCancelModal(props) {
            this.openModal('disputeCancel', props);
        },
        openDepositModal(props) {
            this.openModal('deposit', props);
        },
        openTraderDepositModal(props) {
            this.openModal('traderDeposit', props);
        },
        openWithdrawalModal(props) {
            this.openModal('withdrawal', props);
        },
        openOrderModal(props) {
            this.openModal('order', props);
        },
        openNotificationModal(props) {
            this.openModal('notification', props);
        },
        openPayoutModal(props) {
            this.openModal('payout', props);
        },
        openEditOrderAmountModal(props) {
            this.openModal('editOrderAmount', props);
        },
        openUserNotesModal(props) {
            this.openModal('userNotes', props);
        },
        openUserCreateModal(props) {
            this.openModal('userCreate', props);
        },
        openUserEditModal(props) {
            this.openModal('userEdit', props);
        },
        openPaymentDetailCreateModal(props) {
            this.openModal('paymentDetailCreate', props);
        },
        openPaymentDetailEditModal(props) {
            this.openModal('paymentDetailEdit', props);
        },
        openMerchantCreateModal(props) {
            this.openModal('merchantCreate', props);
        },
        openPaymentCreateModal(props) {
            this.openModal('paymentCreate', props);
        },
        openSupportCreateModal(props) {
            this.openModal('supportCreate', props);
        },
        openSupportEditModal(props) {
            this.openModal('supportEdit', props);
        },
        openPromoCodeCreateModal(props) {
            this.openModal('promoCodeCreate', props);
        },
        openPromoCodeEditModal(props) {
            this.openModal('promoCodeEdit', props);
        },
        closeAll() {
            for (const modal_name in this.modals) {
                this.closeModal(modal_name)
            }
        }
    },
})
