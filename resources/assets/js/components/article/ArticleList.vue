<template>
	<div>
		<li v-for="article in articles" :class="article.cover_path?'content-item have-img':'content-item'">
			<a v-if="article.cover_path" class="wrap-img" :href="'/article/'+article.id"   >
				<img :src="article.cover" :alt="article.cover">
				<span v-if="article.type =='video'" class="rotate-play">
		        <i class="iconfont icon-shipin"></i>
		      </span>
				<i  v-if="article.type =='video'" class="duration">{{ article.duration }}</i>  <!--当为视频时,取出视频的时长 -->
			</a>
			<div class="content">
				<div v-if="article.type !=='article'" class="author">
					<a v-if="article.user" class="avatar"    :href="'/user/'+article.user.id">
						<img :src="article.user.avatar" alt="">
					</a>
					<div class="info">
						<a v-if="article.user" class="nickname"    :href="'/user/'+article.user_id">{{ article.user.name }}</a>
						<img v-if="article.user.is_signed" class="badge-icon" src="https://diudie-1251052432.cos.ap-guangzhou.myqcloud.com/images/signed.png" data-toggle="tooltip" data-placement="top" title="签约作者" alt="签约作者">
						<img v-if="article.user.is_editor" class="badge-icon" src="https://diudie-1251052432.cos.ap-guangzhou.myqcloud.com/images/editor.png" data-toggle="tooltip" data-placement="top" title="小编" alt="小编">
						<span class="time">{{ article.time_ago }}</span>
					</div>
				</div>

				<a v-if="article.type == 'article'" class="title"    :href="'/article/'+article.id">
					<span>{{ article.title }}</span>
				</a>
				<a class="abstract":href="'/article/'+article.id"  >{{ article.description}}</a>

				<div class="meta">
					<a v-if="article.category" class="category"    :href="'/category/' + article.category.id">
						<i class="iconfont icon-zhuanti1"></i>
						{{ article.category.name }}
					</a>
					<a v-if="article.user" class="nickname"    :href="'/user/'+article.user.id">{{ article.user.name }}</a>
					<a   :href="'/article/'+article.id">
						<i class="iconfont icon-liulan"></i> {{ article.hits }}
					</a>
					<a    :href="'/article/'+article.id+'/#comments'" class="comment_meta">
						<i class="iconfont icon-svg37"></i> {{ article.count_replies }}
					</a>
					<span><i class="iconfont icon-03xihuan"></i> {{ article.count_likes }}</span>
					<span class="hidden-xs" v-if="article.count_tips>0"><i class="iconfont icon-qianqianqian"></i> {{ article.count_tips }}</span>
				</div>
			</div>
		</li>
		<loading-more  v-if="articles.length || notEmpty" :end="end"></loading-more>
		<div v-else class="unMessage">
			<blank-content></blank-content>
		</div>
	</div>
</template>

<script>
	export default {
		name: "ArticleList",

		props: ["api", "startPage", "notEmpty", "isDesktop"],


		watch: {

			api(val) {
				this.clear();
				this.fetchData();
			}
		},
		computed: {
			apiUrl() {
				var page = this.page;
				var api = this.api ? this.api : this.apiDefault;
				var api_url = api.indexOf("?") !== -1 ? api + "&page=" + page : api + "?page=" + page;
				return api_url;
			}
		},

		mounted() {
			this.listenScrollBottom();
			this.fetchData();

		},
		methods: {
			clear() {
				this.articles = [];
			},
			listenScrollBottom() {
				var m = this;
				$(window).on("scroll", function() {
					var aheadMount = 5; //sometimes need ahead a little ...
					var is_scroll_to_bottom = $(this).scrollTop() >= $("body").height() - $(window).height() - aheadMount;
					if (is_scroll_to_bottom) {
						m.fetchMore();
					}
				});
			},

			fetchMore() {
				++this.page;
				if (this.lastPage > 0 && this.page > this.lastPage) {
					//OPTIMIZE: ui 提示  ...
					return;
				}
				this.fetchData();
			},

			fetchData() {
				var m = this;
				//OPTIMIZE:: ui show loading ....
				window.axios.get(this.apiUrl).then(function(response) {
					m.articles = m.articles.concat(response.data.data);
					m.lastPage = response.data.last_page;
					$('[data-toggle="tooltip"]').tooltip();
					if (m.page >= m.lastPage) {
						m.end = true;
					}
					//OPTIMIZE:: ui show loading done !!!
				});
			}
		},

		data() {
			return {
				apiDefault: "",
				page: this.startPage ? this.startPage : 1,
				lastPage: -1,
				articles: [],
				end: false,
				videotime: "1:30" //OPTIMIZE:: 视频长度应该从Api为每个item获取，不是在这里
			};
		}
	};
</script>

<style lang="css" scoped>
</style>
