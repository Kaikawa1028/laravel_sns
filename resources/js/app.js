import './bootstrap'
import Vue from 'vue'
import CommentList from './components/CommentList'
import ArticleLike from './components/ArticleLike'
import ArticleTagsInput from './components/ArticleTagsInput'
import FollowButton from './components/FollowButton'
import Message from './components/Message'

const app = new Vue({
    el: '#app',
    components: {
        ArticleLike,
        ArticleTagsInput,
        FollowButton,
        CommentList,
        Message,
    }
})