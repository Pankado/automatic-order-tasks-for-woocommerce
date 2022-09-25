const ajaxAPI = () => {
  let cache_map = {};

  function doAjax(data = {}, method = 'GET', cacheID) {

    return new Promise((resolve, reject) => {
      if (cacheID && cache_map[cacheID]) { // get cached data if any, to avoid unnecessary ajax calls.
        resolve(cache_map[cacheID]);
        return;
      }

      data._ajax_nonce = eam_nonce;

      jQuery.ajax({
        url: ajaxurl,
        method: method,
        data: data,
        dataType: 'json',
        success: function(data) {
          if (cacheID) {
            cache_map[cacheID] = data;
          }
          resolve(data);
        },
        error: function(error) {
          reject(error);
        }
      });
    });
  }

  return {

    getOrderManagementConfig: (id) => {
      return doAjax( {
        action: 'eam_get_order_tasks_config',
        id: id
      } );
    },

    saveOrderStatusConfig: (data) => {
      return doAjax( {
        action: 'eam_post_order_tasks_config',
        data: data
      }, 'POST' );
    },

    getPostCategories: () => {
      return doAjax( {
        action: 'eam_get_post_categories'
      }, 'GET', 'post_categories' );
    },

    getUsers: () => {
      return doAjax( {
        action: 'eam_get_users'
      }, 'GET', 'users' );
    },

    getShippingMethods: () => {
      return doAjax( {
        action: 'eam_get_shipping_methods'
      }, 'GET', 'shipping_methods' );
    }
  }
}

const instance = ajaxAPI();
export default instance;