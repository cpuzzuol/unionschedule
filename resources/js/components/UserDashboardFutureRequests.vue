<template>
    <div class="user-dashboard-outstanding-requests-container">
        <template v-if="outstandingRequests.length == 0">
            <v-alert
                class="mt-3"
                color="info"
                :value="true"
                dark
            >
                You have no future vacation requests.
            </v-alert>
        </template>
        <v-list v-else dense>
            <v-list-item v-for="(req, index) in outstandingRequests" :key="'req-pending-' + index">
                <v-list-item-title>{{ req.date_requested | slashdatedow }}</v-list-item-title>
                <v-list-item-subtitle><span :class="pastDecisionColor(req.decision)">{{ pastDecisionText(req.decision) }}</span></v-list-item-subtitle>
                <v-list-item-icon>
                    <user-cancel-vacation-request-modal v-if="req.decision == 'pending'" :user="user" :vacation-request="req" @request-canceled="handleRequestCanceled"></user-cancel-vacation-request-modal>
                </v-list-item-icon>
            </v-list-item>
        </v-list>
    </div>
</template>
<script>
    import UserCancelVacationRequestModal from "./UserCancelVacationRequestModal"
	export default {
		props: {
			outstandingRequests: {
				type: Array,
                required: true
            },
			user: {
				type: Object,
				required: true
			}
		},
        components: { UserCancelVacationRequestModal },
		created() {

		},
		data: () => ({

		}),
		computed: {
		},
		methods: {
            handleRequestCanceled() {
                this.$emit('request-canceled')
            },
            pastDecisionColor(decision) {
                switch(decision) {
                    case 'approved':
                        return 'success--text'
                    case 'denied':
                        return 'error--text'
                    default:
                        return ''
                }
            },
            pastDecisionText(decision) {
                switch(decision) {
                    case 'approved':
                        return 'Approved'
                    case 'denied':
                        return 'Denied'
                    default:
                        return 'Pending Review'
                }
            },
		},
		watch: {
		}
	}
</script>
