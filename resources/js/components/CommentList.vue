<template>
    <div>
        <div class="card" v-for="(comment, index) in comments" :key="index">
            <div class="card-body d-flex flex-row">
                <a href="#" class="text-dark">
                    <i class="fas fa-user-circle fa-3x mr-1"></i>
                </a>
                <div>
                    <a href="#" class="text-dark">
                        <div class="font-weight-bold">{{ comment.user.name }}</div>
                    </a>
                    <div class="font-weight-lighter">{{ comment.created_at }}</div>
                </div>

            </div>
            <div class="card-body pt-0">
                <div class="card-text">
                    {{ comment.text }}
                </div>
            </div>
        </div>
        <div class="card" v-if="isLogin">
            <div class="card-body">
                <div class="form-group">
                    <textarea name="text" v-model="text" required class="form-control" rows="4" placeholder="本文"></textarea>
                </div>
                <button @click="postComment" class="btn btn-primary">コメントする</button>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "CommentList",
        props: {
            commentUrl: {
                type: String,
                default: "",
            },
            isLogin: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                comments: [],
                csrf: document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),

                text: "",
                apiToken: document
                    .querySelector('meta[name="api-token"]')
                    .getAttribute("content"),
            }
        },
        mounted() {
            axios.get(this.commentUrl).then(res =>  {
                this.comments = res.data;
            });
        },
        methods: {
            postComment() {
                var comment = {
                    '_token' : this.csrf,
                    'text'   : this.text
                }

                axios.post(this.commentUrl, comment, {
                    headers: {
                        'Authorization' : 'Bearer '+ this.apiToken,
                    }
                }).then(res =>  {
                   this.comments = res.data;
                   this.text = "";
                });
            }
        }
    }
</script>

<style scoped>

</style>