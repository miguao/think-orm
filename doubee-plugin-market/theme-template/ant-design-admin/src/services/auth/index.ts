import request from "@/utils/request";

/**
 * 用户登录
 * @param data
 */
export function login(data: any) {
  return request.post("/auth/login", data);
}
