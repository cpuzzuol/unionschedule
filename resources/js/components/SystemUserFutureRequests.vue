<template>
    <div class="request-history-container">
        <template v-if="futureRequests.length == 0">
            <v-alert
                class="mt-3"
                color="info"
                :value="true"
                dark
            >
                This person has no future vacation requests.
            </v-alert>
        </template>
        <v-list v-else dense>
            <v-list-item v-for="(req, index) in futureRequests" :key="'req-pending-' + index">
                <v-list-item-title>{{ req.date_requested | slashdatedow }}</v-list-item-title>
                <v-list-item-subtitle>
                    <template v-if="req.decision == 'pending'">
                        <admin-manage-vacation-request-modal action="approve" :user="user" :vacation-request="req" @request-updated="handleRequestUpdated"></admin-manage-vacation-request-modal>
                        <admin-manage-vacation-request-modal action="deny" :user="user" :vacation-request="req" @request-updated="handleRequestUpdated"></admin-manage-vacation-request-modal>
                    </template>
                    <span v-else :class="decisionColor(req.decision)">{{ decisionText(req.decision) }}</span>
                </v-list-item-subtitle>
                <v-list-item-icon>
                    <system-user-vacation-request-log-modal :vacation-request="req"></system-user-vacation-request-log-modal>
                </v-list-item-icon>
            </v-list-item>
        </v-list>
    </div>
</template>
<script>
    import AdminManageVacationRequestModal from "./AdminManageVacationRequestModal"
    import SystemUserVacationRequestLogModal from "./SystemUserVacationRequestLogModal";
	export default {
		props: {
			futureRequests: {
				type: Array,
                required: true
            },
			user: {
				type: Object,
				required: true
			}
		},
        components: { AdminManageVacationRequestModal, SystemUserVacationRequestLogModal },
		created() {

		},
		data: () => ({

		}),
		computed: {
		},
		methods: {
            decisionColor(decision) {
                switch(decision) {
                    case 'approved':
                        return 'success--text'
                    case 'denied':
                        return 'error--text'
                    default:
                        return ''
                }
            },
            decisionText(decision) {
                switch(decision) {
                    case 'approved':
                        return 'Approved'
                    case 'denied':
                        return 'Denied'
                    default:
                        return 'No Action'
                }
            },
			handleRequestUpdated() {
                this.$emit('request-updated')
            }
		},
		watch: {
		}
	}
</script>
<style type="scss" scoped>
    .capitalize {
        text-transform: capitalize;
    }
</style>
