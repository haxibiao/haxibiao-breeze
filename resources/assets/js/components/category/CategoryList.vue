<template>
  <div>
    <li v-for="category in categories" class="col-sm-4 recommend-card">
      <div>
        <a   :href="'/category/'+category.id">
          <img class="avatar-category" :src="category.logo?category.logo:'http://haxibiao-1251052432.cos.ap-guangzhou.myqcloud.com/images/collection.png'" alt />
          <h4 class="name">{{ category.name }}</h4>
          <p class="category-description">{{ category.description }}</p>
          </a>    
          <follow 
            type="categories" 
            :id="category.id" 
            :user-id="user_id" 
            :followed="category.followed">
          </follow>
        <hr>
        <div class="count"><a   :href="'/category/'+category.id">{{ category.count }}篇作品</a> · {{ category.count_follows }}人关注</div>
      </div>
    </li>
    <div class="clear"></div>
    <a class="btn-base btn-more" href="javascript:;">{{ page >= lastPage ? '已经到底了':'正在加载更多' }}...</a>
  </div>  
</template> 

<script>
export default {
  name: "CategoryList",

  props: ["api", "startPage"],

  computed: {
    apiUrl() {
      var page = this.page;
      var api = this.api ? this.api : this.apiDefault;
      var api_url = api.indexOf("?") !== -1 ? api + "&page=" + page : api + "?page=" + page;
      return api_url;
    },
    user_id() {
      return window.user.id;
    }
  },

  mounted() {
    this.fetchData();
    this.listenScrollBottom();
  },

  methods: {
    listenScrollBottom() {
      var _this = this;
      $(window).on("scroll", function() {
        var aheadMount = 5; //sometimes need ahead a little ...
        var is_scroll_to_bottom = $(this).scrollTop() >= $("body").height() - $(window).height() - aheadMount;
        if (is_scroll_to_bottom) {
          _this.fetchMore();
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
      var _this = this;
      //OPTIMIZE:: loading ....
      window.axios.get(this.apiUrl).then(function(response) {
        _this.categories = _this.categories.concat(response.data.data);
        _this.lastPage = response.data.last_page;

        //OPTIMIZE:: loading done !!!
      });
    }
  },

  data() {
    return {
      apiDefault: "",
      page: this.startPage ? this.startPage : 1,
      lastPage: -1,
      categories: []
    };
  }
};
</script>

<style lang="css" scoped>
    .clear{
      clear: both
    }
</style>
