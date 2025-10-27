type AnyObject = Record<string, any>;

const tree = {
  generate<T extends AnyObject>(
    array: T[],
    primaryKey: string = "id",
    parentKey: string = "parent_id",
    childrenName: string = "children"
  ): T[] {
    const items: Record<string | number, T> = {};
    const resultTree: T[] = [];

    array.forEach((item) => {
      items[item[primaryKey]] = { ...item };
    });

    Object.values(items).forEach((item) => {
      const parentId = item[parentKey];
      if (parentId && items[parentId]) {
        const parent = items[parentId];
        (parent[childrenName] as T[] | undefined) ??= [];
        (parent[childrenName] as T[]).push(item);
      } else {
        resultTree.push(item);
      }
    });

    return resultTree;
  },
};

export default tree;
