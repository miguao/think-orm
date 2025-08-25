import request from "@/utils/request";

/**
 * 上传文件
 * @param file 文件对象
 * @param config 配置参数
 * @returns Promise<any>
 */
export async function uploadFile(file: File, config: any) {
    const formData = new FormData();
    formData.append('file', file);
    const response = await request.post('/upload/upload', formData, config);
    if (response.data.code === 200 && response.data.data) {
        return response.data.data;
    }

    return Promise.reject(new Error(response.data.message));
}