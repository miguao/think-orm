import request from "@/utils/request";

/**
 * 获取我的信息
 */
export function getMeInfo() {
  return request.get("/account/getMeInfo");
}
