<template>
    <div class="request-history-container">
        <template v-if="pendingRequests.length == 0">
            <v-alert
                class="mt-3"
                color="info"
                :value="true"
                dark
            >
                This person has no pending vacation requests.
            </v-alert>
        </template>
        <v-list v-else dense two-lines>
            <v-list-item v-for="(req, index) in pendingRequests" :key="'req-pending-' + index">
                <v-list-item-title>{{ req.date_requested | slashdatedow }}</v-list-item-title>
                <v-list-item-subtitle>{{ req.requester.first_name + ' ' + req.requester.last_name }}</v-list-item-subtitle>
                <v-list-item-icon>
                    <admin-manage-vacation-request-modal action="approve" :user="user" :vacation-request="req" @request-updated="handleRequestUpdated"></admin-manage-vacation-request-modal>
                    <admin-manage-vacation-request-modal action="deny" :user="user" :vacation-request="req" @request-updated="handleRequestUpdated"></admin-manage-vacation-request-modal>
                </v-list-item-icon>
            </v-list-item>
        </v-list>
    </div>
</template>
<script>
    import AdminManageVacationRequestModal from "./AdminManageVacationRequestModal"
	export default {
		props: {
			pendingRequests: {
				type: Array,
                required: true
            },
			user: {
				type: Object,
				required: true
			}
		},
        components: { AdminManageVacationRequestModal },
		created() {

		},
		data: () => ({

		}),
		computed: {
		},
		methods: {
            handleRequestUpdated() {
                this.$emit('request-updated')
            }
		},
		watch: {
		}
	}
</script>
